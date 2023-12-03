<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Personal\Manager;

use App\Models\Order;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use Telegram\Bot\Keyboard\Keyboard;

class EditOrder extends BaseInlineActionAbstract
{
    public function process()
    {
        $parts = explode(':', $this->relatedObject->data);

        $orderId = $parts[array_key_last($parts)];

        $order = Order::find($orderId);

        if (!$order) {
            $this->answer('Не могу найти заказ', true);
            return;
        }

        $unknownStatuses = [
            'Непонятно',
            'Не знаю',
            'Спроси попозже',
            'Не в курсе'
        ];

        $text = 'Заказ №' . $orderId . PHP_EOL . PHP_EOL .
            'Статус: ' . match ($order->status) {
                Status::CREATED => 'Создан',
                Status::PROCESSING => 'В работе',
                Status::READY => 'Готов к выдаче',
                Status::FINISHED => 'Выдан',
                Status::CANCELLED => 'Отменён',
                default => $unknownStatuses[array_rand($unknownStatuses)]
            } . PHP_EOL .
            'Состав:' . PHP_EOL;

        foreach ($order->items()->get() as $item) {
            $itemText = $item->name . ' - ' . $item->price . ' ' . $item->priceType()->first()->name . PHP_EOL;
            $text .= $itemText;
        }

        $keyboard = [];

        if ($order->status === Status::CREATED) {
            $keyboard[] = Keyboard::inlineButton(
                ['text' => 'Отменить заказ', 'callback_data' => 'personal:cancel-order:' . $orderId]
            );
        }

        $keyboard[] = Keyboard::inlineButton(
            ['text' => 'К списку заказов', 'callback_data' => 'personal:order-list']
        );

        $this->telegram->editMessageText([
            'message_id' => $this->relatedObject->message->messageId,
            'chat_id' => $this->relatedObject->message->chat->id,
            'text' => $text,
            'reply_markup' => Keyboard::make(
                [
                    'inline_keyboard' => [$keyboard]
                ]
            )
        ]);

        $this->answer();
    }
}
