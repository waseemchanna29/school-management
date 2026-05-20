<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\ParentRecord;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEducationRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class StudentController extends Controller
{
    private function campusId(): int { return CampusContext::id(); }

    public function index(Request $request)
    {
        $query = Student::where('campus_id', $this->campusId())->with(['schoolClass', 'section']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('full_name', 'like', "%$s%")
                ->orWhere('roll_number', 'like', "%$s%")
                ->orWhere('gr_number', 'like', "%$s%")
                ->orWhere('father_name', 'like', "%$s%"));
        }

        if ($request->filled('class_id')) $query->where('class_id', $request->class_id);
        if ($request->filled('status'))   $query->where('status', $request->status);

        $students = $query->latest()->paginate(15);
        $classes  = SchoolClass::where('campus_id', $this->campusId())->where('is_active', true)->orderBy('name')->get();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $campusId = $this->campusId();
        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->with('sections')->orderBy('name')->get();
        $sections = Section::where('campus_id', $campusId)->where('is_active', true)->with('schoolClass')->get();
        return view('admin.students.create', compact('classes', 'sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'            => ['required', 'email', 'unique:users,email'],
            'password'         => ['required', 'confirmed', Password::min(8)],
            'full_name'        => ['required', 'string'],
            'father_name'      => ['required', 'string'],
            'mother_name'      => ['required', 'string'],
            'gender'           => ['required', 'in:Male,Female,Other'],
            'date_of_birth'    => ['required', 'date'],
            'address'          => ['required', 'string'],
            'city'             => ['required', 'string'],
            'district'         => ['required', 'string'],
            'province'         => ['required', 'string'],
            'class_id'         => ['required', 'exists:classes,id'],
            'section_id'       => ['required', 'exists:sections,id'],
            'admission_date'   => ['required', 'date'],
            'photo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'father_full_name' => ['required', 'string'],
            'father_phone'     => ['required', 'string'],
            'mother_full_name' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request) {
            $campusId = $this->campusId();
            $count    = Student::where('campus_id', $campusId)->count() + 1;
            $roll     = 'S-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            $gr       = 'GR-' . date('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

            $user = User::create([
                'name'     => $request->full_name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'student',
            ]);

            $photoPath = $request->hasFile('photo')
                ? $request->file('photo')->store('students', 'public') : null;

            $student = Student::create(array_merge(
                $request->except(['email','password','password_confirmation','photo',
                    'father_full_name','father_cnic','father_phone','father_occupation','father_qualification','father_income','father_is_alive',
                    'mother_full_name','mother_cnic','mother_phone','mother_occupation','mother_qualification','mother_is_alive',
                    'guardian_name','guardian_relation','guardian_phone','guardian_cnic','guardian_address','education','_token']),
                ['user_id' => $user->id, 'campus_id' => $campusId,
                 'roll_number' => $roll, 'gr_number' => $gr,
                 'photo' => $photoPath, 'status' => 'active']
            ));

            ParentRecord::create(array_merge(
                $request->only(['father_full_name','father_cnic','father_phone','father_occupation','father_qualification','father_income',
                    'mother_full_name','mother_cnic','mother_phone','mother_occupation','mother_qualification',
                    'guardian_name','guardian_relation','guardian_phone','guardian_cnic','guardian_address']),
                ['student_id' => $student->id,
                 'father_is_alive' => $request->boolean('father_is_alive', true),
                 'mother_is_alive' => $request->boolean('mother_is_alive', true)]
            ));

            if ($request->filled('education')) {
                foreach ($request->education as $edu) {
                    StudentEducationRecord::create(array_merge($edu, ['student_id' => $student->id]));
                }
            }
        });

        return redirect()->route('admin.students.index')->with('success', 'Student enrolled successfully.');
    }

    public function show(Student $student)
    {
        $this->authorize($student);
        $student->load(['user','schoolClass','section','parentRecord','educationRecords']);
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $this->authorize($student);
        $student->load(['parentRecord','educationRecords']);
        $campusId = $this->campusId();
        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->with('sections')->orderBy('name')->get();
        $sections = Section::where('campus_id', $campusId)->where('is_active', true)->get();
        return view('admin.students.edit', compact('student', 'classes', 'sections'));
    }

    public function update(Request $request, Student $student)
    {
        $this->authorize($student);
        $request->validate([
            'full_name'        => ['required', 'string'],
            'father_name'      => ['required', 'string'],
            'mother_name'      => ['required', 'string'],
            'gender'           => ['required', 'in:Male,Female,Other'],
            'date_of_birth'    => ['required', 'date'],
            'address'          => ['required', 'string'],
            'city'             => ['required', 'string'],
            'district'         => ['required', 'string'],
            'province'         => ['required', 'string'],
            'class_id'         => ['required', 'exists:classes,id'],
            'section_id'       => ['required', 'exists:sections,id'],
            'admission_date'   => ['required', 'date'],
            'status'           => ['required', 'in:active,inactive,transferred,expelled'],
            'photo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'father_full_name' => ['required', 'string'],
            'father_phone'     => ['required', 'string'],
            'mother_full_name' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request, $student) {
            if ($request->hasFile('photo')) {
                if ($student->photo) Storage::disk('public')->delete($student->photo);
                $student->photo = $request->file('photo')->store('students', 'public');
            }

            $student->update($request->except(['email','password','photo','_token','_method',
                'father_full_name','father_cnic','father_phone','father_occupation','father_qualification','father_income','father_is_alive',
                'mother_full_name','mother_cnic','mother_phone','mother_occupation','mother_qualification','mother_is_alive',
                'guardian_name','guardian_relation','guardian_phone','guardian_cnic','guardian_address']));

            $student->parentRecord()->updateOrCreate(
                ['student_id' => $student->id],
                $request->only(['father_full_name','father_cnic','father_phone','father_occupation','father_qualification','father_income',
                    'mother_full_name','mother_cnic','mother_phone','mother_occupation','mother_qualification',
                    'guardian_name','guardian_relation','guardian_phone','guardian_cnic','guardian_address'])
                + ['father_is_alive' => $request->boolean('father_is_alive', true),
                   'mother_is_alive' => $request->boolean('mother_is_alive', true)]
            );

            $student->user->update(['name' => $request->full_name]);
        });

        return redirect()->route('admin.students.show', $student)->with('success', 'Student updated.');
    }

    public function destroy(Student $student)
    {
        $this->authorize($student);
        $student->user->delete();
        return redirect()->route('admin.students.index')->with('success', 'Student removed.');
    }

    private function authorize(Student $student): void
    {
        if ($student->campus_id !== $this->campusId()) abort(403);
    }
}