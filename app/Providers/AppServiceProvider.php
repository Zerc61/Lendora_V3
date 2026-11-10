<?php

namespace App\Providers;

use App\Enums\BookingStatus;
use App\Enums\QueueTicketStatus;
use App\Models\Booking;
use App\Models\QueueTicket;
use App\Services\ActivityLogger;
use App\Services\SettingService;
use App\Services\ShiftService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.borrower', 'layouts.admin'], function ($view): void {
            $user = auth()->user();
            $count = 0;

            if ($user !== null && Schema::hasTable('notifications')) {
                $count = $user->unreadNotifications()->count();
            }

            $view->with('unreadNotifications', $count);
        });

        View::composer('layouts.borrower', function ($view): void {
            $user = auth()->user();

            if ($user !== null && $user->role?->slug === 'borrower' && Schema::hasTable('bookings')) {
                $activeBookingCount = Booking::query()
                    ->where('borrower_id', $user->id)
                    ->where('status', BookingStatus::Active->value)
                    ->count();

                $view->with('activeBookingCount', $activeBookingCount);
                $view->with('borrowerCompliance', $user->compliance_score ?? 100);
            } else {
                $view->with('activeBookingCount', 0);
                $view->with('borrowerCompliance', 100);
            }
        });

        View::composer('layouts.borrower', function ($view): void {
            $settings = app(SettingService::class);
            $view->with('operationalHours', $settings->get('operational_hours', 'Senin–Jumat 08:00–17:00 WIB'));
            $view->with('orgName', $settings->get('organization_name', 'Unit Kampus'));
            $view->with('operatorPhone', $settings->get('operator_contact_phone', 'Ext. 402'));
            $view->with('operatorWhatsApp', $settings->get('operator_contact_whatsapp', '+62 812-3456-7890'));
        });

        View::composer('layouts.admin', function ($view): void {
            $user = auth()->user();
            $role = $user?->role?->slug;
            $sidebarCounts = [];
            $adminShift = null;

            if ($user !== null && in_array($role, ['operator', 'organization_admin'], true)) {
                $adminShift = app(ShiftService::class)->current($user->organization_id);

                if (Schema::hasTable('bookings')) {
                    $sidebarCounts['approvals'] = Booking::query()
                        ->where('organization_id', $user->organization_id)
                        ->where('status', BookingStatus::Pending->value)
                        ->count();
                }

                if (Schema::hasTable('queue_tickets')) {
                    $sidebarCounts['queue'] = QueueTicket::query()
                        ->where('organization_id', $user->organization_id)
                        ->whereDate('service_date', now()->toDateString())
                        ->where('status', QueueTicketStatus::Waiting->value)
                        ->count();
                }
            }

            $view->with('adminShift', $adminShift);
            $view->with('sidebarCounts', $sidebarCounts);
        });

        Event::listen(Login::class, function (Login $event): void {
            if (! Schema::hasTable('activity_logs')) {
                return;
            }

            app(ActivityLogger::class)->log(
                'auth.login',
                $event->user,
                "Pengguna {$event->user->name} masuk ke aplikasi.",
                $event->user,
            );
        });
    }
}
