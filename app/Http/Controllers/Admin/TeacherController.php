<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class TeacherController extends Controller
{
    private function campusId(): int { return CampusContext::id(); }

    public function index(Request $request)
    {
        $query = Teacher::where('campus_id', $this->campusId())->with('user');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('full_name', 'like', "%$s%")
                ->orWhere('employee_code', 'like', "%$s%")
                ->orWhere('cnic', 'like', "%$s%"));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $teachers = $query->latest()->paginate(15);
        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        $subjects = Subject::where('campus_id', $this->campusId())->where('is_active', true)->with('schoolClass')->get();
        return view('admin.teachers.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name'       => ['required', 'string', 'max:255'],
            'father_name'     => ['required', 'string', 'max:255'],
            'cnic'            => ['required', 'string', 'max:15', 'unique:teachers,cnic'],
            'phone'           => ['required', 'string', 'max:15'],
            'emergency_phone' => ['nullable', 'string'],
            'gender'          => ['required', 'in:Male,Female,Other'],
            'date_of_birth'   => ['required', 'date'],
            'religion'        => ['nullable', 'string'],
            'nationality'     => ['required', 'string'],
            'domicile'        => ['nullable', 'string'],
            'address'         => ['required', 'string'],
            'city'            => ['required', 'string'],
            'district'        => ['required', 'string'],
            'province'        => ['required', 'string'],
            'qualification'   => ['required', 'string'],
            'specialization'  => ['nullable', 'string'],
            'joining_date'    => ['required', 'date'],
            'employment_type' => ['required', 'in:Permanent,Contract,Visiting,Part-time'],
            'salary'          => ['nullable', 'numeric'],
            'bank_name'       => ['nullable', 'string'],
            'bank_account'    => ['nullable', 'string'],
            'photo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'email'           => ['required', 'email', 'unique:users,email'],
            'password'        => ['required', 'confirmed', Password::min(8)],
            'subjects'        => ['nullable', 'array'],
            'subjects.*'      => ['exists:subjects,id'],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->full_name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'teacher',
            ]);

            $photoPath = $request->hasFile('photo')
                ? $request->file('photo')->store('teachers', 'public') : null;

            $teacher = Teacher::create(array_merge(
                $request->except(['email','password','password_confirmation','subjects','photo','_token']),
                ['user_id' => $user->id, 'campus_id' => $this->campusId(),
                 'employee_code' => 'TCH-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                 'photo' => $photoPath]
            ));

            if ($request->filled('subjects')) {
                $teacher->subjects()->sync($request->subjects);
            }
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher added successfully.');
    }

    public function show(Teacher $teacher)
    {
        $this->authorize($teacher);
        $teacher->load(['user', 'subjects.schoolClass']);
        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $this->authorize($teacher);
        $teacher->load('subjects');
        $subjects = Subject::where('campus_id', $this->campusId())->where('is_active', true)->with('schoolClass')->get();
        return view('admin.teachers.edit', compact('teacher', 'subjects'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $this->authorize($teacher);
        $request->validate([
            'full_name'       => ['required', 'string'],
            'father_name'     => ['required', 'string'],
            'cnic'            => ['required', 'string', 'unique:teachers,cnic,' . $teacher->id],
            'phone'           => ['required', 'string'],
            'gender'          => ['required', 'in:Male,Female,Other'],
            'date_of_birth'   => ['required', 'date'],
            'address'         => ['required', 'string'],
            'city'            => ['required', 'string'],
            'district'        => ['required', 'string'],
            'province'        => ['required', 'string'],
            'qualification'   => ['required', 'string'],
            'joining_date'    => ['required', 'date'],
            'employment_type' => ['required'],
            'photo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'subjects'        => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($request, $teacher) {
            if ($request->hasFile('photo')) {
                if ($teacher->photo) Storage::disk('public')->delete($teacher->photo);
                $teacher->photo = $request->file('photo')->store('teachers', 'public');
            }
            $teacher->update($request->except(['email','password','password_confirmation','subjects','photo','_token','_method']));
            $teacher->subjects()->sync($request->subjects ?? []);
            $teacher->user->update(['name' => $request->full_name]);
        });

        return redirect()->route('admin.teachers.show', $teacher)->with('success', 'Teacher updated.');
    }

    public function destroy(Teacher $teacher)
    {
        $this->authorize($teacher);
        $teacher->user->delete();
        return redirect()->route('admin.teachers.index')->with('success', 'Teacher removed.');
    }

    private function authorize(Teacher $teacher): void
    {
        if ($teacher->campus_id !== $this->campusId()) abort(403);
    }
}