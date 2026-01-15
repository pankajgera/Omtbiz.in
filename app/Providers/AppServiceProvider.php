<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::useBootstrapThree();
        Passport::enablePasswordGrant();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Laravel\Passport\Console\InstallCommand::class,
                \Laravel\Passport\Console\KeysCommand::class,
                \Laravel\Passport\Console\ClientCommand::class,
            ]);
        }
    }
}
