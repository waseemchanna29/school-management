<?php

use App\Http\Controllers\Admin\CampusSelectController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\Fee\CampusSettingController;
use App\Http\Controllers\Admin\Fee\FeeSchedulerController;
use App\Http\Controllers\Admin\Fee\StudentFeeController;
use App\Http\Controllers\Admin\Fee\FeeInvoiceController;
use App\Http\Controllers\Admin\Fee\FeePaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\SuperAdmin\AdminUserController;
use App\Http\Controllers\SuperAdmin\CampusController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperDashboard;
use App\Http\Controllers\Admin\Timetable\TimetableController;
use App\Http\Controllers\Admin\Timetable\TimetablePeriodController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboard;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendance;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendance;
use App\Http\Controllers\SuperAdmin\GradeScaleController as SuperGradeScale;
use App\Http\Controllers\SuperAdmin\ExamWeightController as SuperExamWeight;
use App\Http\Controllers\Admin\GradeScaleController as AdminGradeScale;
use App\Http\Controllers\Admin\PerformanceController as AdminPerformance;
use App\Http\Controllers\Teacher\PerformanceController as TeacherPerformance;
use App\Http\Controllers\AcademicYearSelectController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\EnrollmentController;

use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', fn() => redirect()->route('login'));

// ─── Guest ──────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout')->middleware('auth');



// ─── Campus Selection (admin only) ──────────────────────────────────────────
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/campus/select',   [CampusSelectController::class, 'show'])->name('campus.select');
    Route::post('/campus/select',  [CampusSelectController::class, 'select'])->name('campus.select.post');
    Route::get('/campus/switch',   [CampusSelectController::class, 'switchCampus'])->name('campus.switch');

    Route::get('/academic-year/select',  [AcademicYearSelectController::class, 'show'])->name('academic-year.select');
    Route::post('/academic-year/select', [AcademicYearSelectController::class, 'select'])->name('academic-year.select.post');
    Route::get('/academic-year/switch',  [AcademicYearSelectController::class, 'switchYear'])->name('academic-year.switch');
});

// ─── Super Admin ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'super_admin'])->prefix('super')->name('super.')->group(function () {
    Route::get('/dashboard', [SuperDashboard::class, 'index'])->name('dashboard');

    // Campuses
    Route::resource('campuses', CampusController::class);
    Route::post('/campuses/{campus}/assign-admin',        [CampusController::class, 'assignAdmin'])->name('campuses.assign-admin');
    Route::delete('/campuses/{campus}/admins/{user}',     [CampusController::class, 'removeAdmin'])->name('campuses.remove-admin');

    // Admin Users
    Route::resource('admins', AdminUserController::class)->except('show');

    Route::prefix('grading')->name('grading.')->group(function () {
        Route::get('/',                        [SuperGradeScale::class, 'index'])->name('index');
        Route::get('/create',                  [SuperGradeScale::class, 'create'])->name('create');
        Route::post('/',                       [SuperGradeScale::class, 'store'])->name('store');
        Route::get('/{gradeScale}/edit',       [SuperGradeScale::class, 'edit'])->name('edit');
        Route::put('/{gradeScale}',            [SuperGradeScale::class, 'update'])->name('update');
        Route::delete('/{gradeScale}',         [SuperGradeScale::class, 'destroy'])->name('destroy');

        Route::get('/weights',                 [SuperExamWeight::class, 'index'])->name('weights');
        Route::post('/weights',                [SuperExamWeight::class, 'store'])->name('weights.store');
        Route::put('/weights/{examTypeWeight}', [SuperExamWeight::class, 'update'])->name('weights.update');
        Route::delete('/weights/{examTypeWeight}', [SuperExamWeight::class, 'destroy'])->name('weights.destroy');
    });
});


// Academic year management itself does NOT have year_selected middleware
// (admin needs to access it before selecting a year)
// It only needs campus_selected:
Route::middleware(['auth', 'admin', 'campus_selected'])
    ->prefix('admin')->name('admin.')->group(function () {

        Route::prefix('enrollment')->name('enrollment.')->group(function () {
            Route::get('/',                      [EnrollmentController::class, 'index'])->name('index');
            Route::get('/enroll',                [EnrollmentController::class, 'create'])->name('create');
            Route::post('/enroll',               [EnrollmentController::class, 'store'])->name('store');
            Route::get('/admission',             [EnrollmentController::class, 'admissionCreate'])->name('admission');
            Route::post('/admission',            [EnrollmentController::class, 'admissionStore'])->name('admission.store');
            Route::get('/carry-forward',         [EnrollmentController::class, 'carryForwardCreate'])->name('carry-forward');
            Route::post('/carry-forward',        [EnrollmentController::class, 'carryForwardStore'])->name('carry-forward.store');
            Route::post('/bulk-status',          [EnrollmentController::class, 'bulkStatus'])->name('bulk-status');
            Route::get('/{enrollment}/edit',     [EnrollmentController::class, 'edit'])->name('edit');
            Route::put('/{enrollment}',          [EnrollmentController::class, 'update'])->name('update');
            Route::delete('/{enrollment}',       [EnrollmentController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('academic-years')->name('academic-years.')->group(function () {
            Route::get('/',                           [AcademicYearController::class, 'index'])->name('index');
            Route::post('/',                          [AcademicYearController::class, 'store'])->name('store');
            Route::put('/{academicYear}',             [AcademicYearController::class, 'update'])->name('update');
            Route::delete('/{academicYear}',          [AcademicYearController::class, 'destroy'])->name('destroy');
            Route::post('/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])->name('set-current');
            Route::post('/{academicYear}/toggle-lock', [AcademicYearController::class, 'toggleLock'])->name('toggle-lock');
        });
    });

// ─── Admin (campus-scoped) ────────────────────────────────────────────────────
Route::middleware(['auth', 'admin', 'campus_selected', 'year_selected'])->prefix('admin')->name('admin.')->group(function () {

    Route::prefix('academic-years')->name('academic-years.')->group(function () {
        Route::get('/',                           [AcademicYearController::class, 'index'])->name('index');
        Route::post('/',                          [AcademicYearController::class, 'store'])->name('store');
        Route::put('/{academicYear}',             [AcademicYearController::class, 'update'])->name('update');
        Route::delete('/{academicYear}',          [AcademicYearController::class, 'destroy'])->name('destroy');
        Route::post('/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])->name('set-current');
        Route::post('/{academicYear}/toggle-lock', [AcademicYearController::class, 'toggleLock'])->name('toggle-lock');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('students', StudentController::class);
    Route::resource('teachers', TeacherController::class);

    Route::get('/classes',              [ClassController::class,   'index'])->name('classes.index');
    Route::post('/classes',             [ClassController::class,   'store'])->name('classes.store');
    Route::put('/classes/{class}',      [ClassController::class,   'update'])->name('classes.update');
    Route::delete('/classes/{class}',   [ClassController::class,   'destroy'])->name('classes.destroy');

    Route::get('/sections',             [SectionController::class, 'index'])->name('sections.index');
    Route::post('/sections',            [SectionController::class, 'store'])->name('sections.store');
    Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

    Route::get('/subjects',             [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects',            [SubjectController::class, 'store'])->name('subjects.store');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    Route::post('/dashboard/generate-monthly-invoices', [DashboardController::class, 'generateMonthlyInvoices'])->name('dashboard.generate-monthly');

    Route::post(
        '/sections/{section}/assign-teacher',
        [SectionController::class, 'assignClassTeacher']
    )->name('sections.assign-teacher');

    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/',                              [AdminAttendance::class, 'index'])->name('index');
        Route::get('/report',                        [AdminAttendance::class, 'report'])->name('report');
        Route::get('/student/{student}',             [AdminAttendance::class, 'studentAttendance'])->name('student');
        Route::get('/{session}',                     [AdminAttendance::class, 'show'])->name('show');
        Route::post('/{session}/update-session',     [AdminAttendance::class, 'updateSession'])->name('update-session');
        Route::post('/{session}/student/{student}',  [AdminAttendance::class, 'updateRecord'])->name('update-record');
        Route::post('/{session}/unlock',             [AdminAttendance::class, 'unlock'])->name('unlock');
        Route::post('/{session}/lock',               [AdminAttendance::class, 'lock'])->name('lock');
        Route::delete('/{session}',                  [AdminAttendance::class, 'destroy'])->name('destroy');
    });
    // ─── Fee Management ──────────────────────────────────────────────────────────
    Route::prefix('fee')->name('fee.')->group(function () {

        // Campus settings (logo etc.)
        Route::get('/settings',              [CampusSettingController::class, 'edit'])->name('settings');
        Route::post('/settings',             [CampusSettingController::class, 'update'])->name('settings.update');
        Route::delete('/settings/logo',      [CampusSettingController::class, 'removeLogo'])->name('settings.remove-logo');

        // Schedulers
        Route::get('/schedulers',            [FeeSchedulerController::class, 'index'])->name('schedulers.index');
        Route::get('/schedulers/create',     [FeeSchedulerController::class, 'create'])->name('schedulers.create');
        Route::post('/schedulers',           [FeeSchedulerController::class, 'store'])->name('schedulers.store');
        Route::get('/schedulers/{scheduler}',        [FeeSchedulerController::class, 'show'])->name('schedulers.show');
        Route::get('/schedulers/{scheduler}/edit',   [FeeSchedulerController::class, 'edit'])->name('schedulers.edit');
        Route::put('/schedulers/{scheduler}',        [FeeSchedulerController::class, 'update'])->name('schedulers.update');
        Route::delete('/schedulers/{scheduler}',     [FeeSchedulerController::class, 'destroy'])->name('schedulers.destroy');
        Route::post('/schedulers/{scheduler}/toggle', [FeeSchedulerController::class, 'toggle'])->name('schedulers.toggle');

        // Student fee profile
        Route::get('/student/{student}',             [StudentFeeController::class, 'show'])->name('student.show');
        Route::post('/student/{student}/assign',     [StudentFeeController::class, 'assign'])->name('student.assign');
        Route::post('/student/{student}/add-item',   [StudentFeeController::class, 'addItem'])->name('student.add-item');
        Route::post('/student/{student}/unassign',   [StudentFeeController::class, 'unassign'])->name('student.unassign');
        Route::put('/student-item/{item}',           [StudentFeeController::class, 'updateItem'])->name('student.update-item');
        Route::delete('/student-item/{item}',        [StudentFeeController::class, 'removeItem'])->name('student.remove-item');

        // Invoices
        Route::get('/invoices',                      [FeeInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create',               [FeeInvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices',                     [FeeInvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/bulk',                 [FeeInvoiceController::class, 'bulkCreate'])->name('invoices.bulk');
        Route::post('/invoices/bulk',                [FeeInvoiceController::class, 'bulkStore'])->name('invoices.bulk-store');
        Route::get('/invoices/{invoice}',            [FeeInvoiceController::class, 'show'])->name('invoices.show');
        Route::put('/invoices/{invoice}/adjust',     [FeeInvoiceController::class, 'adjust'])->name('invoices.adjust');
        Route::post('/invoices/{invoice}/waive',     [FeeInvoiceController::class, 'waive'])->name('invoices.waive');
        Route::delete('/invoices/{invoice}',         [FeeInvoiceController::class, 'destroy'])->name('invoices.destroy');

        // Payments
        Route::post('/invoices/{invoice}/payments',  [FeePaymentController::class, 'store'])->name('payments.store');
        Route::delete('/payments/{payment}',         [FeePaymentController::class, 'destroy'])->name('payments.destroy');
    });

    Route::prefix('timetable')->name('timetable.')->group(function () {

        // Timetable CRUD
        Route::get('/',                   [TimetableController::class, 'index'])->name('index');
        Route::get('/create',             [TimetableController::class, 'create'])->name('create');
        Route::post('/',                  [TimetableController::class, 'store'])->name('store');
        Route::get('/{timetable}',        [TimetableController::class, 'show'])->name('show');
        Route::get('/{timetable}/edit',   [TimetableController::class, 'edit'])->name('edit');
        Route::post('/{timetable}/grid',  [TimetableController::class, 'saveGrid'])->name('save-grid');
        Route::delete('/{timetable}',     [TimetableController::class, 'destroy'])->name('destroy');
        Route::post('/{timetable}/toggle', [TimetableController::class, 'toggleActive'])->name('toggle');

        // Teacher view
        Route::get('/teacher/{teacher}',  [TimetableController::class, 'teacherView'])->name('teacher-view');

        // Periods (nested under timetable)
        Route::post(
            '/{timetable}/periods',
            [TimetablePeriodController::class, 'store']
        )->name('periods.store');
        Route::put(
            '/{timetable}/periods/{period}',
            [TimetablePeriodController::class, 'update']
        )->name('periods.update');
        Route::delete(
            '/{timetable}/periods/{period}',
            [TimetablePeriodController::class, 'destroy']
        )->name('periods.destroy');
        Route::post(
            '/{timetable}/periods/reorder',
            [TimetablePeriodController::class, 'reorder']
        )->name('periods.reorder');
    });

    Route::prefix('grading')->name('grading.')->group(function () {
        Route::get('/',                          [AdminGradeScale::class, 'index'])->name('index');
        Route::post('/copy-scale',               [AdminGradeScale::class, 'copyGlobalScale'])->name('copy-scale');
        Route::get('/{gradeScale}/edit',         [AdminGradeScale::class, 'edit'])->name('edit');
        Route::put('/{gradeScale}',              [AdminGradeScale::class, 'update'])->name('update');
        Route::delete('/{gradeScale}',           [AdminGradeScale::class, 'destroyScale'])->name('destroy');
        Route::post('/copy-weights',             [AdminGradeScale::class, 'copyGlobalWeights'])->name('copy-weights');
        Route::put('/weights/{weight}',          [AdminGradeScale::class, 'updateWeight'])->name('weights.update');
    });

    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/',                          [AdminPerformance::class, 'index'])->name('index');
        Route::get('/class-report',              [AdminPerformance::class, 'classReport'])->name('class-report');
        Route::get('/student/{student}',         [AdminPerformance::class, 'studentReport'])->name('student-report');
    });
});


// ─── Teacher Panel ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {

    Route::get('/academic-year/select',  [AcademicYearSelectController::class, 'show'])->name('academic-year.select');
    Route::post('/academic-year/select', [AcademicYearSelectController::class, 'select'])->name('academic-year.select.post');
    Route::get('/academic-year/switch',  [AcademicYearSelectController::class, 'switchYear'])->name('academic-year.switch');

    Route::get('/dashboard', [TeacherDashboard::class, 'index'])->name('dashboard');

    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/take',                    [TeacherAttendance::class, 'take'])->name('take');
        Route::post('/save',                   [TeacherAttendance::class, 'save'])->name('save');
        Route::post('/{session}/submit',       [TeacherAttendance::class, 'submit'])->name('submit');
        Route::get('/history',                 [TeacherAttendance::class, 'history'])->name('history');
        Route::get('/{session}',               [TeacherAttendance::class, 'show'])->name('show');
        Route::get('/report/students',         [TeacherAttendance::class, 'studentReport'])->name('student-report');
    });

    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/subjects',                  [TeacherPerformance::class, 'subjects'])->name('subjects');
        Route::get('/marks/{subject}',           [TeacherPerformance::class, 'enterMarks'])->name('enter-marks');
        Route::post('/marks/{subject}',          [TeacherPerformance::class, 'saveMarks'])->name('save-marks');
        Route::get('/history',                   [TeacherPerformance::class, 'history'])->name('history');
    });
});
