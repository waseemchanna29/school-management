<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class ApiRoleMiddleware
{
    use ApiResponse;

    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return $this->unauthorized('Token is invalid or expired.');
        }

        if (!$user) {
            return $this->unauthorized('User not found.');
        }

        // Only teacher and student can access the API
        if (!in_array($user->role, ['teacher', 'student'])) {
            return $this->error('Access denied. This API is for teachers and students only.', 403);
        }

        return $next($request);
    }
}