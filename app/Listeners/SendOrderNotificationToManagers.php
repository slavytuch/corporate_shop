<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\User;
use App\Slavytuch\Shop\Global\Enums\UserPermissions;
use App\Slavytuch\Shop\Global\Enums\UserRole;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

class SendOrderNotificationToManagers
{
    public function __construct(private Api $telegram)
    {
    }

    public function handle(OrderCreated $event): void
    {
        $allManagers = User::permission(UserPermissions::ACCESS_ALL_ORDERS->value)->get();

        foreach ($allManagers as $manager) {
            if ($manager->id == $event->order->user_id) {
                continue;
            }

            $this->telegram->sendMessage([
                'chat_id' => $manager->chat_id,
                'text' => 'Прилетел новый заказ №' . $event->order->id,
                'reply_markup' => Keyboard::make([
                    'inline_keyboard' => [
                        [
                            Keyboard::inlineButton([
                                'text' => 'Открыть заказ',
                                'callback_data' => 'personal:display-order:' . $event->order->id
                            ])
                        ]
                    ]
                ])
            ]);
        }
    }
}
