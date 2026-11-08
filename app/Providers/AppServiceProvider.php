<?php

namespace App\Providers;

use App\Services\ActivityLogger;
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
        View::composer('layouts.borrower', function ($view): void {
            $user = auth()->user();
            $count = 0;

            if ($user !== null && Schema::hasTable('notifications')) {
                $count = $user->unreadNotifications()->count();
            }

            $view->with('unreadNotifications', $count);
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
