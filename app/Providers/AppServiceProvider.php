<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        // Gunakan komponen Pagination Bootstrap 5
        Paginator::useBootstrapFive();

        // Audit Kinerja Kueri Basis Data: Log seluruh kueri SQL untuk pembuktian mitigasi N+1 Query
        DB::listen(function ($query) {
            Log::info(sprintf(
                "[SQL AUDIT] (%.2f ms) %s [Bindings: %s]",
                $query->time,
                $query->sql,
                json_encode($query->bindings)
            ));
        });
    }
}
