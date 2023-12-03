<?php

namespace App\Providers;

use App\Slavytuch\Telegram\Webhook\WebhookManager;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->when(WebhookManager::class)
            ->needs(LoggerInterface::class)
            ->give(fn() => \Log::channel('webhook'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
