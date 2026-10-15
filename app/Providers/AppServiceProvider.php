<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        config(['voting.default' => Arr::only(config('voting'), Setting::KEYS)]);

        try {
            Setting::terapkanKeConfig();
        } catch (Throwable) {
            // Tabel settings belum dimigrasi (mis. saat instalasi): pakai nilai bawaan config/voting.php.
        }
    }
}
