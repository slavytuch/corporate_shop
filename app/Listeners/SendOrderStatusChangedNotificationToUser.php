<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Slavytuch\Shop\Order\Utils\OrderLang;
use Telegram\Bot\Api;

class SendOrderStatusChangedNotificationToUser
{
    public function __construct(protected Api $telegram)
    {
    }


    /**
     * Отправляем заказчику уведомление что его заказ поменялся
    */
    public function handle(OrderStatusChanged $event): void
    {
        $user = $event->order->user()->first();

        $this->telegram->sendMessage([
            'chat_id' => $user->chat_id,
            'text' => 'Заказ № ' . $event->order->id .  ' перешёл в новый статус: ' . PHP_EOL .
                OrderLang::getStatusName($event->oldValue) . ' -> ' . OrderLang::getStatusName($event->order->status)
        ]);
    }
}
