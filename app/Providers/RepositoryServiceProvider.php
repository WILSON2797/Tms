<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\CustomerRepositoryInterface::class,
            \App\Repositories\Eloquent\CustomerRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\DriverRepositoryInterface::class,
            \App\Repositories\Eloquent\DriverRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\VehicleRepositoryInterface::class,
            \App\Repositories\Eloquent\VehicleRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TransporterRepositoryInterface::class,
            \App\Repositories\Eloquent\TransporterRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\ShipmentOrderRepositoryInterface::class,
            \App\Repositories\Eloquent\ShipmentOrderRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TripRepositoryInterface::class,
            \App\Repositories\Eloquent\TripRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
