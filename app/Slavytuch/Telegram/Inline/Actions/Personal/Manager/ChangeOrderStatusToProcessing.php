<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Personal\Manager;

use App\Models\Order;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Shop\Order\Exceptions\OrderServiceException;
use App\Slavytuch\Shop\Order\OrderService;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Keyboards\OrderDisplayKeyboard;
use Telegram\Bot\Keyboard\Keyboard;

class ChangeOrderStatusToProcessing extends BaseInlineActionAbstract
{
    public function process()
    {
        $parts = explode(':', $this->relatedObject->data);

        $order = Order::find($parts[array_key_last($parts)]);

        if (!$order) {
            $this->answer('Не могу найти заказ');
            return;
        }

        $orderService = app(OrderService::class);
        try {
            $orderService->startProcessing($order);
            $order->refresh();

            $this->telegram->editMessageText([
                'chat_id' => $this->relatedObject->message->chat->id,
                'message_id' => $this->relatedObject->message->messageId,
                'text' => $orderService->makeOrderDisplayText($order),
                'reply_markup' => (new OrderDisplayKeyboard($order))->getKeyboard()
            ]);

            $this->answer();
        } catch (OrderServiceException $ex) {
            $this->answer($ex->getMessage());
        }
    }
}
