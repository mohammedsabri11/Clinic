<?php

namespace App\Providers;

use App\Repositories\AppointmentRepository;
use App\Repositories\UserRepository;
use App\Services\AppointmentService;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AppointmentRepository::class, function ($app) {
            return new AppointmentRepository(new \App\Models\Appointment());
        });

        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository(new \App\Models\User());
        });

        $this->app->bind(AppointmentService::class, function ($app) {
            return new AppointmentService(
                $app->make(AppointmentRepository::class),
                $app->make(UserRepository::class)
            );
        });

        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService($app->make(UserRepository::class));
        });
    }

    public function boot()
    {
        //
    }
}