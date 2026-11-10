<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AssetController as AdminAssetController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HandoverController as AdminHandoverController;
use App\Http\Controllers\Admin\InspectionController as AdminInspectionController;
use App\Http\Controllers\Admin\KioskController as AdminKioskController;
use App\Http\Controllers\Admin\MaintenanceController as AdminMaintenanceController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PenaltyController as AdminPenaltyController;
use App\Http\Controllers\Admin\PracticumScheduleController as AdminPracticumScheduleController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\RecommendationController as AdminRecommendationController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\ShiftController as AdminShiftController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Borrower\BookingController as BorrowerBookingController;
use App\Http\Controllers\Borrower\BookingExtensionController as BorrowerBookingExtensionController;
use App\Http\Controllers\Borrower\CatalogController as BorrowerCatalogController;
use App\Http\Controllers\Borrower\FavoriteController as BorrowerFavoriteController;
use App\Http\Controllers\Borrower\HelpController as BorrowerHelpController;
use App\Http\Controllers\Borrower\HomeController as BorrowerHomeController;
use App\Http\Controllers\Borrower\NotificationController as BorrowerNotificationController;
use App\Http\Controllers\Borrower\PenaltyController as BorrowerPenaltyController;
use App\Http\Controllers\Borrower\PaymentController as BorrowerPaymentController;
use App\Http\Controllers\Borrower\ProfileController as BorrowerProfileController;
use App\Http\Controllers\Borrower\TwoFactorController as BorrowerTwoFactorController;
use App\Http\Controllers\Kiosk\QueueController as KioskQueueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : view('welcome');
});

Route::get('/dashboard', function () {
    return redirect(auth()->user()->hasRole('borrower') ? '/borrower' : '/admin');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'active', 'role:borrower'])
    ->prefix('borrower')
    ->name('borrower.')
    ->group(function () {
        Route::get('/', BorrowerHomeController::class)->name('home');
        Route::get('catalog', [BorrowerCatalogController::class, 'index'])->name('catalog.index');
        Route::get('catalog/{asset}', [BorrowerCatalogController::class, 'show'])->name('catalog.show');

        Route::get('bookings', [BorrowerBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/create', [BorrowerBookingController::class, 'create'])->name('bookings.create');
        Route::post('bookings', [BorrowerBookingController::class, 'store'])->name('bookings.store');
        Route::get('bookings/{booking}', [BorrowerBookingController::class, 'show'])->name('bookings.show');
        Route::post('bookings/{booking}/cancel', [BorrowerBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::get('bookings/{booking}/document/{type}', [BorrowerBookingController::class, 'document'])->whereIn('type', ['letter', 'bap'])->name('bookings.document');
        Route::get('bookings/{booking}/return-pass', [BorrowerBookingController::class, 'returnPass'])->name('bookings.return-pass');
        Route::post('bookings/{booking}/extensions', [BorrowerBookingExtensionController::class, 'store'])->name('bookings.extensions.store');
        Route::post('bookings/{booking}/extensions/{extension}/cancel', [BorrowerBookingExtensionController::class, 'cancel'])->name('bookings.extensions.cancel');
        Route::get('bookings/{booking}/extension-form', [BorrowerBookingExtensionController::class, 'form'])->name('bookings.extension.form');
        Route::post('bookings/{booking}/extension-request', [BorrowerBookingExtensionController::class, 'store'])->name('bookings.extension.request');

        Route::post('catalog/{asset}/favorite', [BorrowerFavoriteController::class, 'store'])->name('catalog.favorite');
        Route::delete('catalog/{asset}/favorite', [BorrowerFavoriteController::class, 'destroy'])->name('catalog.unfavorite');

        Route::get('notifications', [BorrowerNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{notification}/read', [BorrowerNotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [BorrowerNotificationController::class, 'markAllRead'])->name('notifications.read-all');

        Route::get('help', [BorrowerHelpController::class, '__invoke'])->name('help');

        Route::get('two-factor', [BorrowerTwoFactorController::class, 'edit'])->name('two-factor.edit');
        Route::put('two-factor', [BorrowerTwoFactorController::class, 'update'])->name('two-factor.update');

        Route::get('profile', [BorrowerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [BorrowerProfileController::class, 'update'])->name('profile.update');
    });

// Denda dan pembayaran tetap terbuka untuk borrower yang ditangguhkan agar
// denda dapat dilunasi (lihat docs/rencana/2026-11-09-rencana-borrower-mockup.md §4.17).
Route::middleware(['auth', 'role:borrower'])
    ->prefix('borrower')
    ->name('borrower.')
    ->group(function () {
        Route::get('penalties', [BorrowerPenaltyController::class, 'index'])->name('penalties.index');
        Route::get('penalties/{penalty}', [BorrowerPenaltyController::class, 'show'])->name('penalties.show');
        Route::get('penalties/{penalty}/document/{type}', [BorrowerPenaltyController::class, 'document'])->whereIn('type', ['bap', 'receipt'])->name('penalties.document');
        Route::post('penalties/{penalty}/payments', [BorrowerPaymentController::class, 'store'])->name('penalties.payments.store');
        Route::post('penalties/{penalty}/installment-request', [BorrowerPaymentController::class, 'installment'])->name('penalties.installment.request');

        Route::get('payments/{payment}', [BorrowerPaymentController::class, 'show'])->name('payments.show');
        Route::post('payments/{payment}/proof', [BorrowerPenaltyController::class, 'submitProof'])->name('payments.proof');
    });

Route::middleware('kiosk.token')
    ->prefix('kiosk')
    ->name('kiosk.')
    ->group(function () {
        Route::get('/', [KioskQueueController::class, 'index'])->name('queue');
        Route::post('tickets', [KioskQueueController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('tickets.store');
    });

Route::middleware(['auth', 'active', 'role:operator,organization_admin,admin_prodi,technician'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('search', [AdminSearchController::class, 'index'])->name('search');

        Route::get('profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [AdminProfileController::class, 'update'])->name('profile.update');

        Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');

        Route::get('bookings/calendar', [AdminBookingController::class, 'calendar'])
            ->middleware('role:operator,organization_admin')
            ->name('bookings.calendar');
        Route::get('bookings/calendar/export', [AdminBookingController::class, 'calendarExport'])
            ->middleware('role:operator,organization_admin')
            ->name('bookings.calendar.export');

        Route::middleware('role:operator,organization_admin,admin_prodi')->group(function () {
            Route::get('bookings/approval', [AdminBookingController::class, 'approval'])
                ->name('bookings.approval');
        });

        Route::middleware('role:operator,organization_admin')->group(function () {
            Route::get('bookings/walk-in', [AdminBookingController::class, 'walkInForm'])
                ->name('bookings.walk-in');
            Route::post('bookings/walk-in', [AdminBookingController::class, 'walkInStore'])
                ->name('bookings.walk-in.store');
        });

        Route::middleware('role:operator,organization_admin,admin_prodi')->group(function () {
            Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
            Route::get('bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
            Route::post('bookings/{booking}/approve', [AdminBookingController::class, 'approve'])->name('bookings.approve');

            Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('reports/print', [AdminReportController::class, 'print'])->name('reports.print');
            Route::get('reports/export', [AdminReportController::class, 'export'])->name('reports.export');
        });

        Route::middleware('role:operator,organization_admin,admin_prodi,technician')->group(function () {
            Route::get('assets/export', [AdminAssetController::class, 'export'])
                ->middleware('role:operator,organization_admin')
                ->name('assets.export');
            Route::get('assets', [AdminAssetController::class, 'index'])->name('assets.index');
        });

        Route::middleware('role:operator,organization_admin,admin_prodi')->group(function () {
            Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        });

        Route::middleware('role:admin_prodi')->group(function () {
            Route::get('courses', [AdminCourseController::class, 'index'])->name('courses.index');
            Route::put('courses/{course}/quota', [AdminCourseController::class, 'updateQuota'])->name('courses.quota.update');
            Route::post('courses/transfer', [AdminCourseController::class, 'transfer'])->name('courses.transfer');
            Route::get('practicum', [AdminPracticumScheduleController::class, 'index'])->name('practicum.index');
            Route::post('practicum', [AdminPracticumScheduleController::class, 'store'])->name('practicum.store');
            Route::delete('practicum/{schedule}', [AdminPracticumScheduleController::class, 'destroy'])->name('practicum.destroy');
            Route::get('recommendations', [AdminRecommendationController::class, 'index'])->name('recommendations.index');
            Route::get('reports/semester', [AdminReportController::class, 'semester'])->name('reports.semester');
            Route::get('reports/semester/export', [AdminReportController::class, 'semesterExport'])->name('reports.semester.export');
            Route::get('students', [AdminStudentController::class, 'index'])->name('students.index');
        });

        Route::middleware('role:operator,organization_admin')->group(function () {
            Route::get('kiosk', [AdminKioskController::class, 'index'])->name('kiosk.index');            Route::post('kiosk/lookup', [AdminKioskController::class, 'lookup'])->name('kiosk.lookup');
            Route::post('kiosk/process', [AdminKioskController::class, 'process'])->name('kiosk.process');
            Route::get('kiosk/queue', [AdminKioskController::class, 'queue'])->name('kiosk.queue');
            Route::post('kiosk/queue/call-next', [AdminKioskController::class, 'callNext'])->name('kiosk.queue.call');
            Route::post('kiosk/queue/{ticket}/serve', [AdminKioskController::class, 'serve'])->name('kiosk.queue.serve');
            Route::post('shifts/notes', [AdminShiftController::class, 'store'])->name('shifts.notes.store');

            Route::get('penalties', [AdminPenaltyController::class, 'index'])->name('penalties.index');
            Route::get('penalties/create', [AdminPenaltyController::class, 'create'])->name('penalties.create');
            Route::post('penalties', [AdminPenaltyController::class, 'store'])->name('penalties.store');
            Route::get('penalties/{penalty}', [AdminPenaltyController::class, 'show'])
                ->whereNumber('penalty')
                ->name('penalties.show');
            Route::get('penalties/{penalty}/print', [AdminPenaltyController::class, 'print'])
                ->whereNumber('penalty')
                ->name('penalties.print');
            Route::post('penalties/{penalty}/issue', [AdminPenaltyController::class, 'issue'])
                ->whereNumber('penalty')
                ->name('penalties.issue');
            Route::post('penalties/{penalty}/escalate', [AdminPenaltyController::class, 'escalate'])
                ->whereNumber('penalty')
                ->name('penalties.escalate');
            Route::post('penalties/{penalty}/items', [AdminPenaltyController::class, 'storeItem'])
                ->whereNumber('penalty')
                ->name('penalties.items.store');

            Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
            Route::post('payments/{payment}/verify', [AdminPaymentController::class, 'verify'])
                ->whereNumber('payment')
                ->name('payments.verify');
            Route::post('payments/{payment}/reject', [AdminPaymentController::class, 'reject'])
                ->whereNumber('payment')
                ->name('payments.reject');
        });

        Route::middleware('role:operator,organization_admin')->group(function () {
            Route::post('bookings/{booking}/reject', [AdminBookingController::class, 'reject'])->name('bookings.reject');
            Route::post('bookings/{booking}/suggest-alternative', [AdminBookingController::class, 'suggest'])->name('bookings.suggest');
            Route::post('bookings/{booking}/reassign', [AdminBookingController::class, 'reassign'])->name('bookings.reassign');
            Route::post('bookings/{booking}/extensions/{extension}/approve', [AdminBookingController::class, 'approveExtension'])->name('bookings.extensions.approve');
            Route::post('bookings/{booking}/extensions/{extension}/reject', [AdminBookingController::class, 'rejectExtension'])->name('bookings.extensions.reject');

            Route::get('handovers', [AdminHandoverController::class, 'index'])->name('handovers.index');
            Route::get('bookings/{booking}/receipt/{type}', [AdminHandoverController::class, 'receipt'])
                ->whereIn('type', ['pickup', 'return'])
                ->name('bookings.receipt');

            Route::post('assets/{asset}/retire', [AdminAssetController::class, 'retire'])->name('assets.retire');
            Route::resource('assets', AdminAssetController::class)->except(['show', 'index']);
        });

        Route::middleware('role:operator')->group(function () {
            Route::get('bookings/{booking}/handover', [AdminHandoverController::class, 'show'])->name('bookings.handover');
            Route::post('bookings/{booking}/handover', [AdminHandoverController::class, 'store'])->name('bookings.handover.store');
        });

        Route::middleware('role:organization_admin')->group(function () {
            Route::resource('categories', AdminCategoryController::class)->except(['show', 'index']);
            Route::resource('users', AdminUserController::class)->except('show', 'destroy');
            Route::post('users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
            Route::post('users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');

            Route::get('settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');

            Route::get('activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');
            Route::get('activity-logs/export', [AdminActivityLogController::class, 'export'])->name('activity-logs.export');
        });

        Route::middleware('role:operator,organization_admin,technician')->group(function () {
            Route::get('inspections', [AdminInspectionController::class, 'index'])->name('inspections.index');
            Route::get('inspections/{inspection}', [AdminInspectionController::class, 'show'])
                ->whereNumber('inspection')
                ->name('inspections.show');
            Route::get('maintenances', [AdminMaintenanceController::class, 'index'])->name('maintenances.index');
        });

        Route::middleware('role:operator,technician')->group(function () {
            Route::get('inspections/create', [AdminInspectionController::class, 'create'])->name('inspections.create');
            Route::post('inspections', [AdminInspectionController::class, 'store'])->name('inspections.store');
        });

        Route::middleware('role:operator,organization_admin')->group(function () {
            Route::get('maintenances/create', [AdminMaintenanceController::class, 'create'])->name('maintenances.create');
            Route::post('maintenances', [AdminMaintenanceController::class, 'store'])->name('maintenances.store');
        });

        Route::middleware('role:organization_admin')->group(function () {
            Route::get('maintenances/{maintenance}/edit', [AdminMaintenanceController::class, 'edit'])->name('maintenances.edit');
            Route::put('maintenances/{maintenance}', [AdminMaintenanceController::class, 'update'])->name('maintenances.update');
            Route::delete('maintenances/{maintenance}', [AdminMaintenanceController::class, 'destroy'])->name('maintenances.destroy');
        });

        Route::middleware('role:operator,organization_admin,technician')->group(function () {
            Route::get('maintenances/{maintenance}', [AdminMaintenanceController::class, 'show'])
                ->whereNumber('maintenance')
                ->name('maintenances.show');
            Route::post('maintenances/{maintenance}/parts', [AdminMaintenanceController::class, 'storePart'])
                ->whereNumber('maintenance')
                ->name('maintenances.parts.store');
        });

        Route::middleware('role:technician')->group(function () {
            Route::post('maintenances/{maintenance}/start', [AdminMaintenanceController::class, 'start'])->name('maintenances.start');
            Route::get('maintenances/{maintenance}/finish', [AdminMaintenanceController::class, 'finishForm'])->name('maintenances.finish');
            Route::post('maintenances/{maintenance}/finish', [AdminMaintenanceController::class, 'finish'])->name('maintenances.finish.store');
        });
    });

require __DIR__.'/auth.php';
