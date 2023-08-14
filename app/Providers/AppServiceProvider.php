<?php

namespace App\Providers;

use App\Jobs\DownloadAudio;
use App\Models\Account;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Jobs\DownloadAudio', function ($app) {
            return app(DownloadAudio::class);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::componentNamespace('App\\Http\\Livewire\\Common', 'experior');
        Cashier::useCustomerModel(Account::class);
    }
}
