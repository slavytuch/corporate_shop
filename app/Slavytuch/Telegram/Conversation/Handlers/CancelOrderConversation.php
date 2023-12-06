<?php

namespace App\Slavytuch\Telegram\Conversation\Handlers;

use App\Models\Order;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Shop\Order\Exceptions\OrderServiceException;
use App\Slavytuch\Shop\Order\OrderService;
use App\Slavytuch\Telegram\Conversation\Abstracts\BaseConversationAbstract;
use Telegram\Bot\Keyboard\Keyboard;

class CancelOrderConversation extends BaseConversationAbstract
{
    /**
     * Спрашиваем у пользователя хочет ли он отменить заказ
     */
    public function init(): ?string
    {
        $orderId = $this->history->whereNull('last_stage')->first()?->response;


        $this->reply(
            [
                'text' => 'Отменяем заказ №' . $orderId . '?',
                'reply_markup' => Keyboard::forceReply([
                    'keyboard' => [['Да', 'Нет']],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]
        );

        return 'confirm';
    }

    /**
     * Уточняем
     */
    public function confirm()
    {
        $confirm = $this->telegram->getWebhookUpdate()->getMessage()->text;
        $orderId = $this->history->whereNull('last_stage')->first()?->response;

        switch ($confirm) {
            case 'Да':
                $order = Order::find($orderId);

                try {
                    app(OrderService::class)->cancelOrder($order, false);
                    $this->reply(['text' => 'Заказ отменён!', 'reply_markup' => Keyboard::remove()]);
                } catch (OrderServiceException $ex) {
                    $this->reply(['text' => $ex->getMessage(), 'reply_markup' => Keyboard::remove()]);
                }
                break;
            case 'Нет':
                $this->reply(['text' => 'Хорошо', 'reply_markup' => Keyboard::remove()]);
                break;
            default:
                $this->reply([
                    'text' => 'Мы всё ещё отменяем заказ №' . $orderId,
                    'reply_markup' => Keyboard::forceReply(['keyboard' => [['Да', 'Нет']]]),
                ]);
                return 'confirm';
        }

        return null;
    }

    public function gettingAbandoned()
    {
        $orderId = $this->history->whereNull('last_stage')->first()?->response;

        $this->reply([
            'text' => 'Мы всё ещё отменяем заказ №' . $orderId,
            'reply_markup' => Keyboard::forceReply(['keyboard' => [['Да', 'Нет']]]),
        ]);
    }
}
