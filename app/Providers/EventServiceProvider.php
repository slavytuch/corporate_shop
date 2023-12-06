<?php

namespace App\Providers;

use App\Events\BalanceUpdated;
use App\Events\OrderStatusChanged;
use App\Events\OrderCreated;
use App\Listeners\NotifyUserBalanceUpdated;
use App\Listeners\SendOrderStatusChangedNotificationToUser;
use App\Listeners\SendOrderNotificationToManagers;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderCreated::class => [
            SendOrderNotificationToManagers::class
        ],
        OrderStatusChanged::class => [
            SendOrderStatusChangedNotificationToUser::class,
        ],
        BalanceUpdated::class => [
            NotifyUserBalanceUpdated::class,
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
