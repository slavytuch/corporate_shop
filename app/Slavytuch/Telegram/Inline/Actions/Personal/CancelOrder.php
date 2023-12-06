<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Personal;

use App\Models\Order;
use App\Models\User;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Shop\Order\Exceptions\OrderServiceException;
use App\Slavytuch\Shop\Order\OrderService;
use App\Slavytuch\Telegram\Conversation\ConversationService;
use App\Slavytuch\Telegram\Conversation\Enums\Topic;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Util\UserHelper;
use Illuminate\Support\Collection;
use Telegram\Bot\Keyboard\Keyboard;

class CancelOrder extends BaseInlineActionAbstract
{
    public function process()
    {
        $parts = explode(':', $this->relatedObject->data);

        $orderId = $parts[array_key_last($parts)];

        $order = Order::find($orderId);

        if (!$order) {
            $this->answer('Не могу найти заказ :(');
            return;
        }

        try {
            app(OrderService::class)->checkCancel($order);

            $conversationService = app(ConversationService::class);

            $conversation = $conversationService->createConversation($this->user, Topic::CANCEL_ORDER, $orderId);

            $conversationService->proceedConversation($this->telegram, $conversation);
            $this->answer();
        } catch (OrderServiceException $ex) {
            $this->answer($ex->getMessage());
        }
    }
}
