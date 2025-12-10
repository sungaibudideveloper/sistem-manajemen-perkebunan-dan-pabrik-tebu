<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Repositories\UserManagement\{
    UserRepository,
    JabatanRepository,
    PermissionRepository,
    UserCompanyRepository,
    UserPermissionRepository,
    UserActivityRepository
};

// Services
use App\Services\UserManagement\{
    CacheService,
    UserService,
    JabatanService,
    PermissionService,
    UserCompanyService,
    UserPermissionService,
    UserActivityService
};

class UserManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register Repositories as Singletons
        $this->app->singleton(UserRepository::class);
        $this->app->singleton(JabatanRepository::class);
        $this->app->singleton(PermissionRepository::class);
        $this->app->singleton(UserCompanyRepository::class);
        $this->app->singleton(UserPermissionRepository::class);
        $this->app->singleton(UserActivityRepository::class);

        // Register Services as Singletons
        $this->app->singleton(CacheService::class);
        
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepository::class),
                $app->make(CacheService::class)
            );
        });

        $this->app->singleton(JabatanService::class, function ($app) {
            return new JabatanService(
                $app->make(JabatanRepository::class),
                $app->make(UserRepository::class),
                $app->make(CacheService::class)
            );
        });

        $this->app->singleton(PermissionService::class, function ($app) {
            return new PermissionService(
                $app->make(PermissionRepository::class),
                $app->make(CacheService::class)
            );
        });

        $this->app->singleton(UserCompanyService::class, function ($app) {
            return new UserCompanyService(
                $app->make(UserCompanyRepository::class),
                $app->make(UserRepository::class),
                $app->make(CacheService::class)
            );
        });

        $this->app->singleton(UserPermissionService::class, function ($app) {
            return new UserPermissionService(
                $app->make(UserPermissionRepository::class),
                $app->make(UserRepository::class),
                $app->make(UserCompanyRepository::class),
                $app->make(CacheService::class)
            );
        });

        $this->app->singleton(UserActivityService::class, function ($app) {
            return new UserActivityService(
                $app->make(UserActivityRepository::class),
                $app->make(UserRepository::class),
                $app->make(UserCompanyRepository::class)
            );
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
}