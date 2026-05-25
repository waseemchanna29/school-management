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
});

// ─── Admin (campus-scoped) ────────────────────────────────────────────────────
Route::middleware(['auth', 'admin', 'campus_selected'])->prefix('admin')->name('admin.')->group(function () {
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
});
