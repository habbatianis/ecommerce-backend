<?php
declare(strict_types=1);

namespace App\Providers;

use App\Services\ProjectService\ProjectService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
//       if ($this->app->environment('local')) {
//           $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
//           $this->app->register(TelescopeServiceProvider::class);
//       }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        (new ProjectService)->ensureLicenceCache();
    }
}
