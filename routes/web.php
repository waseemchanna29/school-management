<?php

use App\Http\Controllers\Admin\CampusSelectController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Fee\FeeInvoiceController;
use App\Http\Controllers\Admin\Fee\FeeLabelController;
use App\Http\Controllers\Admin\Fee\FeePaymentController;
use App\Http\Controllers\Admin\Fee\FeeStructureController;
use App\Http\Controllers\Admin\Fee\StudentFeeController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\SuperAdmin\AdminUserController;
use App\Http\Controllers\SuperAdmin\CampusController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperDashboard;
use App\Http\Controllers\Admin\Timetable\PeriodTemplateController;
use App\Http\Controllers\Admin\Timetable\TimetableController;
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
    // ─── Fee Management ────────────────────────────────────────────────────────
    Route::prefix('fee')->name('fee.')->group(function () {

        // Labels
        Route::get('/labels',              [FeeLabelController::class,     'index'])->name('labels.index');
        Route::post('/labels',             [FeeLabelController::class,     'store'])->name('labels.store');
        Route::put('/labels/{feeLabel}',   [FeeLabelController::class,    'update'])->name('labels.update');
        Route::delete('/labels/{feeLabel}', [FeeLabelController::class,   'destroy'])->name('labels.destroy');
        Route::post('/labels/{feeLabel}/toggle', [FeeLabelController::class, 'toggle'])->name('labels.toggle');

        // Structures
        Route::resource('structures', FeeStructureController::class)->except('show');
        Route::get('/structures/{structure}',       [FeeStructureController::class, 'show'])->name('structures.show');
        Route::post('/structures/{structure}/revise', [FeeStructureController::class, 'revise'])->name('structures.revise');

        // Student fees
        Route::get('/students/{student}',            [StudentFeeController::class, 'show'])->name('student.show');
        Route::post('/students/{student}/assign',    [StudentFeeController::class, 'assign'])->name('student.assign');
        Route::post('/students/{student}/add-fee',   [StudentFeeController::class, 'addFee'])->name('student.add-fee');
        Route::put('/student-fees/{studentFee}',     [StudentFeeController::class, 'updateFee'])->name('student.update-fee');
        Route::delete('/student-fees/{studentFee}',  [StudentFeeController::class, 'destroyFee'])->name('student.destroy-fee');

        // Invoices
        Route::get('/invoices',                      [FeeInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create',               [FeeInvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices',                     [FeeInvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}',            [FeeInvoiceController::class, 'show'])->name('invoices.show');
        Route::put('/invoices/{invoice}/adjustments', [FeeInvoiceController::class, 'updateAdjustments'])->name('invoices.adjustments');
        Route::post('/invoices/{invoice}/waive',     [FeeInvoiceController::class, 'waive'])->name('invoices.waive');
        Route::delete('/invoices/{invoice}',         [FeeInvoiceController::class, 'destroy'])->name('invoices.destroy');

        // Payments
        Route::post('/invoices/{invoice}/payments',  [FeePaymentController::class, 'store'])->name('payments.store');
        Route::delete('/payments/{payment}',         [FeePaymentController::class, 'destroy'])->name('payments.destroy');
    });

    Route::prefix('timetable')->name('timetable.')->group(function () {

        // Period Templates (campus-level)
        Route::get('/periods',                      [PeriodTemplateController::class, 'index'])->name('periods.index');
        Route::post('/periods',                     [PeriodTemplateController::class, 'store'])->name('periods.store');
        Route::put('/periods/{periodTemplate}',     [PeriodTemplateController::class, 'update'])->name('periods.update');
        Route::delete('/periods/{periodTemplate}',  [PeriodTemplateController::class, 'destroy'])->name('periods.destroy');
        Route::post('/periods/reorder',             [PeriodTemplateController::class, 'reorder'])->name('periods.reorder');

        // Timetables
        Route::get('/',                             [TimetableController::class, 'index'])->name('index');
        Route::get('/create',                       [TimetableController::class, 'create'])->name('create');
        Route::post('/',                            [TimetableController::class, 'store'])->name('store');
        Route::get('/{timetable}',                  [TimetableController::class, 'show'])->name('show');
        Route::get('/{timetable}/edit',             [TimetableController::class, 'edit'])->name('edit');
        Route::post('/{timetable}/grid',            [TimetableController::class, 'saveGrid'])->name('save-grid');
        Route::delete('/{timetable}',               [TimetableController::class, 'destroy'])->name('destroy');
        Route::post('/{timetable}/toggle',          [TimetableController::class, 'toggleActive'])->name('toggle');

        // Teacher schedule view
        Route::get('/teacher/{teacher}',            [TimetableController::class, 'teacherView'])->name('teacher-view');
    });
});
