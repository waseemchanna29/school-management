<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\ApiPasswordResetOtp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    use ApiResponse;

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/auth/login
    // ────────────────────────────────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        // Only teacher and student can use the API
        if (!in_array($user->role, ['teacher', 'student'])) {
            return $this->error('Access denied. This API is for teachers and students only.', 403);
        }

        $credentials = $request->only('email', 'password');

        try {
             $error_message = null;
            Artisan::call('config:clear');

            if (!$token = JWTAuth::attempt($request->only(['email', 'password']))) {
                $this->checkTooManyFailedAttempts();
                RateLimiter::hit($request->email, 60);
                $error_message = __('Email & Password does not match with our record.');
            }

            if (!empty($error_message)) {
                return $this->error($error_message, Response::HTTP_FORBIDDEN);
            }
             RateLimiter::clear($request->email);
        } catch (JWTException $e) {
            return $this->error('Could not create token. Please try again.', 500);
        }

        if (!$token) {
            return $this->error('Invalid email or password.', 401);
        }

        return $this->success(
            $this->buildAuthPayload($user, $token),
            'Login successful.'
        );
    }


    public function checkTooManyFailedAttempts()
    {
        if (!RateLimiter::tooManyAttempts(request()->email, 5)) {
            return;
        }

        User::where('email', request()->email)->update(['locked' => 1]);
        throw new Exception(__('Account is locked. Too many login attempts.'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/auth/logout
    // ────────────────────────────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        try {
            JWTAuth::parseToken()->invalidate();
            return $this->success(null, 'Logged out successfully.');
        } catch (JWTException $e) {
            return $this->error('Failed to logout. Token may already be invalid.', 400);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/auth/refresh
    // ────────────────────────────────────────────────────────────────────────
    public function refresh(): JsonResponse
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
            $user     = JWTAuth::setToken($newToken)->toUser();

            return $this->success(
                $this->buildAuthPayload($user, $newToken),
                'Token refreshed.'
            );
        } catch (JWTException $e) {
            return $this->error('Token cannot be refreshed. Please login again.', 401);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/auth/me
    // ────────────────────────────────────────────────────────────────────────
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return $this->success(
            $this->buildProfilePayload($user),
            'User profile.'
        );
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/auth/forgot-password
    // ────────────────────────────────────────────────────────────────────────
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user = User::where('email', $request->email)
            ->whereIn('role', ['teacher', 'student'])
            ->first();

        if (!$user) {
            // Return same message to prevent user enumeration
            return $this->success(
                null,
                'If this email exists, a reset code has been sent.'
            );
        }

        // Delete any previous unused OTPs for this email
        ApiPasswordResetOtp::where('email', $request->email)->delete();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        ApiPasswordResetOtp::create([
            'email'      => $request->email,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(15),
            'used'       => false,
        ]);

        // In production: send $otp via email/SMS
        // Mail::to($user->email)->send(new PasswordResetOtpMail($otp));

        return $this->success(
            [
                // Remove 'otp' from response in production — use email/SMS instead
                'otp'        => $otp,
                'expires_in' => '15 minutes',
                'note'       => 'In production, OTP would be sent to email. Included here for testing.',
            ],
            'Reset code generated. Use it within 15 minutes.'
        );
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/auth/reset-password
    // ────────────────────────────────────────────────────────────────────────
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'                 => ['required', 'email'],
            'otp'                   => ['required', 'string', 'size:6'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // Find OTP record
        $otpRecord = ApiPasswordResetOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('used', false)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return $this->error('Invalid OTP code.', 400);
        }

        if ($otpRecord->isExpired()) {
            $otpRecord->delete();
            return $this->error('OTP has expired. Please request a new one.', 400);
        }

        // Find user
        $user = User::where('email', $request->email)
            ->whereIn('role', ['teacher', 'student'])
            ->first();

        if (!$user) {
            return $this->error('User not found.', 404);
        }

        // Update password
        $user->update(['password' => Hash::make($request->password)]);

        // Mark OTP as used and delete
        $otpRecord->update(['used' => true]);
        ApiPasswordResetOtp::where('email', $request->email)->delete();

        // Invalidate all existing JWT tokens for this user
        try {
            JWTAuth::invalidate(JWTAuth::fromUser($user));
        } catch (JWTException $e) {
            // Token may not exist — continue anyway
        }

        return $this->success(
            null,
            'Password reset successfully. Please login with your new password.'
        );
    }

    // ────────────────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────────────────
    private function buildAuthPayload(User $user, string $token): array
    {
        return [
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60, // seconds
            'user'       => $this->buildProfilePayload($user),
        ];
    }

    private function buildProfilePayload(User $user): array
    {
        $base = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ];

        if ($user->isTeacher()) {
            $teacher = $user->teacher()->with([
                'campus',
                'subjects.schoolClass',
            ])->first();

            if ($teacher) {
                $base['profile'] = [
                    'employee_code'   => $teacher->employee_code,
                    'full_name'       => $teacher->full_name,
                    'phone'           => $teacher->phone,
                    'gender'          => $teacher->gender,
                    'qualification'   => $teacher->qualification,
                    'specialization'  => $teacher->specialization,
                    'employment_type' => $teacher->employment_type,
                    'joining_date'    => $teacher->joining_date?->format('Y-m-d'),
                    'campus'          => [
                        'id'   => $teacher->campus?->id,
                        'name' => $teacher->campus?->name,
                        'city' => $teacher->campus?->city,
                    ],
                    'subjects'        => $teacher->subjects->map(fn($s) => [
                        'id'    => $s->id,
                        'name'  => $s->name,
                        'class' => $s->schoolClass?->name,
                    ]),
                    'photo_url' => $teacher->photo
                        ? asset('storage/' . $teacher->photo)
                        : null,
                ];
            }
        }

        if ($user->isStudent()) {
            $student = $user->student()->with([
                'campus',
                'schoolClass',
                'section',
                'parentRecord',
            ])->first();

            if ($student) {
                $base['profile'] = [
                    'roll_number'    => $student->roll_number,
                    'gr_number'      => $student->gr_number,
                    'full_name'      => $student->full_name,
                    'father_name'    => $student->father_name,
                    'mother_name'    => $student->mother_name,
                    'gender'         => $student->gender,
                    'date_of_birth'  => $student->date_of_birth?->format('Y-m-d'),
                    'blood_group'    => $student->blood_group,
                    'phone'          => $student->phone,
                    'status'         => $student->status,
                    'admission_date' => $student->admission_date?->format('Y-m-d'),
                    'campus'         => [
                        'id'   => $student->campus?->id,
                        'name' => $student->campus?->name,
                        'city' => $student->campus?->city,
                    ],
                    'class'   => $student->schoolClass?->name,
                    'section' => $student->section?->name,
                    'parent'  => $student->parentRecord ? [
                        'father_name'  => $student->parentRecord->father_full_name,
                        'father_phone' => $student->parentRecord->father_phone,
                        'mother_name'  => $student->parentRecord->mother_full_name,
                        'mother_phone' => $student->parentRecord->mother_phone,
                    ] : null,
                    'photo_url' => $student->photo
                        ? asset('storage/' . $student->photo)
                        : null,
                ];
            }
        }

        return $base;
    }
}
