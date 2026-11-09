<?php

use App\Http\Controllers\Admin\AssetController as AdminAssetController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HandoverController as AdminHandoverController;
use App\Http\Controllers\Admin\InspectionController as AdminInspectionController;
use App\Http\Controllers\Admin\MaintenanceController as AdminMaintenanceController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Borrower\BookingController as BorrowerBookingController;
use App\Http\Controllers\Borrower\CatalogController as BorrowerCatalogController;
use App\Http\Controllers\Borrower\HomeController as BorrowerHomeController;
use App\Http\Controllers\Borrower\NotificationController as BorrowerNotificationController;
use App\Http\Controllers\Borrower\ProfileController as BorrowerProfileController;
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

        Route::get('notifications', [BorrowerNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{notification}/read', [BorrowerNotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [BorrowerNotificationController::class, 'markAllRead'])->name('notifications.read-all');

        Route::get('profile', [BorrowerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [BorrowerProfileController::class, 'update'])->name('profile.update');
    });

Route::middleware(['auth', 'active', 'role:operator,organization_admin,admin_prodi,technician'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('bookings/calendar', [AdminBookingController::class, 'calendar'])
            ->middleware('role:operator,organization_admin')
            ->name('bookings.calendar');

        Route::middleware('role:operator,organization_admin,admin_prodi')->group(function () {
            Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
            Route::get('bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
            Route::post('bookings/{booking}/approve', [AdminBookingController::class, 'approve'])->name('bookings.approve');

            Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('reports/export', [AdminReportController::class, 'export'])->name('reports.export');
        });

        Route::middleware('role:operator,organization_admin')->group(function () {
            Route::post('bookings/{booking}/reject', [AdminBookingController::class, 'reject'])->name('bookings.reject');

            Route::get('bookings/{booking}/handover', [AdminHandoverController::class, 'show'])->name('bookings.handover');
            Route::post('bookings/{booking}/handover', [AdminHandoverController::class, 'store'])->name('bookings.handover.store');

            Route::post('assets/{asset}/retire', [AdminAssetController::class, 'retire'])->name('assets.retire');
            Route::resource('assets', AdminAssetController::class)->except('show');
        });

        Route::middleware('role:organization_admin')->group(function () {
            Route::resource('categories', AdminCategoryController::class)->except('show');
            Route::resource('users', AdminUserController::class)->except('show', 'destroy');
            Route::post('users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
            Route::post('users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');

            Route::get('settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');
        });

        Route::middleware('role:operator,organization_admin,technician')->group(function () {
            Route::resource('inspections', AdminInspectionController::class)->only(['index', 'create', 'store']);
            Route::get('maintenances', [AdminMaintenanceController::class, 'index'])->name('maintenances.index');
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

        Route::middleware('role:technician')->group(function () {
            Route::post('maintenances/{maintenance}/start', [AdminMaintenanceController::class, 'start'])->name('maintenances.start');
            Route::get('maintenances/{maintenance}/finish', [AdminMaintenanceController::class, 'finishForm'])->name('maintenances.finish');
            Route::post('maintenances/{maintenance}/finish', [AdminMaintenanceController::class, 'finish'])->name('maintenances.finish.store');
        });
    });

require __DIR__.'/auth.php';
