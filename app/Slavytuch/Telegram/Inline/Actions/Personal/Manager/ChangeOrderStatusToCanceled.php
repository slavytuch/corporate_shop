<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Personal\Manager;

use App\Models\Order;
use App\Slavytuch\Shop\Order\Exceptions\OrderServiceException;
use App\Slavytuch\Shop\Order\OrderService;
use App\Slavytuch\Shop\Order\Utils\OrderLang;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionFunction;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionProcedure;
use App\Slavytuch\Telegram\Inline\Factories\InlineActionFactory;
use Telegram\Bot\Keyboard\Keyboard;

class ChangeOrderStatusToCanceled extends BaseInlineActionAbstract
{
    public function process()
    {
        $parts = explode(':', $this->relatedObject->data);

        $order = Order::find($parts[array_key_last($parts)]);

        if (!$order) {
            $this->answer('Не могу найти заказ', true);
            return;
        }

        try {
            $orderService = app(OrderService::class);
            $orderService->cancelOrder($order);
            $order->refresh();

            $this->telegram->editMessageText([
                'chat_id' => $this->relatedObject->message->chat->id,
                'message_id' => $this->relatedObject->message->messageId,
                'text' => $orderService->makeOrderDisplayText($order),
                'reply_markup' => Keyboard::make([
                    'inline_keyboard' => [
                        [
                            Keyboard::inlineButton(
                                ['text' => 'К списку заказов', 'callback_data' => ActionProcedure::ALL_ORDERS->value]
                            )
                        ]
                    ]
                ])
            ]);
            $this->answer('Заказ отменён', true);
        } catch (OrderServiceException $ex) {
            $this->answer($ex->getMessage());
        }
    }
}
