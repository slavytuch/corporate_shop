<?php

namespace App\Listeners;

use App\Events\BalanceUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class NotifyUserBalanceUpdated
{
    public function __construct(protected Api $telegram)
    {
        //
    }

    /**
     * Уведомляем пользователя
     *
     * @param BalanceUpdated $event
     * @throws TelegramSDKException
     */
    public function handle(BalanceUpdated $event): void
    {
        $user = $event->balance->user()->first();
        $type = $event->balance->priceType()->first();

        $this->telegram->sendMessage([
            'chat_id' => $user->chat_id,
            'text'  => 'Произошло измение счёта:' . PHP_EOL .
            $type->name . ' ' . $event->oldValue . ' -> ' . $type->name . ' ' . $event->balance->amount
        ]);
    }
}
