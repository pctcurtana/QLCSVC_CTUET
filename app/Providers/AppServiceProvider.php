<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Interfaces
use App\Contracts\Repositories\CoSoRepositoryInterface;
use App\Contracts\Repositories\KhuNhaRepositoryInterface;
use App\Contracts\Repositories\PhongRepositoryInterface;
use App\Contracts\Repositories\ThietBiRepositoryInterface;
use App\Contracts\Repositories\LichSuBaoDuongRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;

// Repository Implementations
use App\Repositories\CoSoRepository;
use App\Repositories\KhuNhaRepository;
use App\Repositories\PhongRepository;
use App\Repositories\ThietBiRepository;
use App\Repositories\LichSuBaoDuongRepository;
use App\Repositories\PermissionRepository;

// Services
use App\Services\CoSoService;
use App\Services\KhuNhaService;
use App\Services\PhongService;
use App\Services\ThietBiService;
use App\Services\LichSuBaoDuongService;
use App\Services\DashboardService;
use App\Services\PermissionService;
use App\Services\AuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register Repository bindings
        $this->app->bind(CoSoRepositoryInterface::class, CoSoRepository::class);
        $this->app->bind(KhuNhaRepositoryInterface::class, KhuNhaRepository::class);
        $this->app->bind(PhongRepositoryInterface::class, PhongRepository::class);
        $this->app->bind(ThietBiRepositoryInterface::class, ThietBiRepository::class);
        $this->app->bind(LichSuBaoDuongRepositoryInterface::class, LichSuBaoDuongRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);

        // Services: bind concrete to itself for clarity (optional, container can resolve automatically)
        $this->app->bind(CoSoService::class, CoSoService::class);
        $this->app->bind(KhuNhaService::class, KhuNhaService::class);
        $this->app->bind(PhongService::class, PhongService::class);
        $this->app->bind(ThietBiService::class, ThietBiService::class);
        $this->app->bind(LichSuBaoDuongService::class, LichSuBaoDuongService::class);
        $this->app->bind(DashboardService::class, DashboardService::class);
        $this->app->bind(PermissionService::class, PermissionService::class);
        $this->app->bind(AuthService::class, AuthService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
