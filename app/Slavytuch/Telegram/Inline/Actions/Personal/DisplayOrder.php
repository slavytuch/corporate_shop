<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Personal;

use App\Models\Order;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Shop\Order\OrderService;
use App\Slavytuch\Shop\Order\Utils\OrderLang;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Keyboards\OrderDisplayKeyboard;
use Telegram\Bot\Keyboard\Keyboard;

class DisplayOrder extends BaseInlineActionAbstract
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

        $this->telegram->editMessageText([
            'message_id' => $this->relatedObject->message->messageId,
            'chat_id' => $this->relatedObject->message->chat->id,
            'text' => app(OrderService::class)->makeOrderDisplayText($order),
            'reply_markup' => (new OrderDisplayKeyboard($order))->getKeyboard()
        ]);

        $this->answer();
    }
}
