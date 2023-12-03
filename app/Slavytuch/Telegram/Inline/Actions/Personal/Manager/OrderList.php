<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Personal\Manager;

use App\Models\Order;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use Telegram\Bot\Keyboard\Keyboard;

class OrderList extends BaseInlineActionAbstract
{
    public function process()
    {
        $orderList = Order::all();

        if ($orderList->isEmpty()) {
            $this->answer('Заказов пока нет', true);
            return;
        }

        $keyboard = [];
        foreach ($orderList as $order) {
            $text = 'Заказ №' . $order->id . PHP_EOL;
            $items = $order->items()->get();
            foreach ($items as $item) {
                $text .= $item->name . ' - ' . $item->price . ' ' . $item->priceType()->first()->name . PHP_EOL;
            }
            $keyboard[] = [
                Keyboard::inlineButton(
                    ['text' => $text, 'callback_data' => 'personal:display-order:' . $order->id]
                )
            ];
        }
        $this->telegram->editMessageText([
            'message_id' => $this->relatedObject->message->messageId,
            'chat_id' => $this->relatedObject->message->chat->id,
            'text' => 'Список заказов:',
            'reply_markup' => Keyboard::make(['inline_keyboard' => $keyboard])
        ]);

        $this->answer();
    }
}
