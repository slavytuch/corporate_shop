<?php

namespace App\Slavytuch\Telegram\Conversation\Handlers;

use App\Models\Order;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Telegram\Conversation\Abstracts\BaseConversationAbstract;
use Telegram\Bot\Keyboard\Keyboard;

class CancelOrderConversation extends BaseConversationAbstract
{
    /**
     * Спрашиваем у пользователя хочет ли он отменить заказ
     */
    public function init(): ?string
    {
        $orderId = $this->history->where('next_stage', 'init')->first()?->response;


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

        switch ($confirm) {
            case 'Да':

                $orderId = $this->history->where('next_stage', 'init')->first()?->response;
                $order = Order::find($orderId);

                $balances = $this->user->balances()->get();

                foreach ($order->items()->get() as $item) {
                    $balance = $balances->where('price_type_id', $item->price_type_id)->first();
                    $balance->amount += $item->price;
                    $balance->save();
                }

                $order->status = Status::CANCELLED;
                $order->save();

                $this->reply(['text' => 'Заказ отменён!', 'reply_markup' => Keyboard::remove()]);
                break;
            case 'Нет':
                $this->reply(['text' => 'Хорошо', 'reply_markup' => Keyboard::remove()]);
                break;
            default:
                $this->reply([
                    'text' => 'Мы всё ещё отменяем заказ?',
                    'reply_markup' => Keyboard::forceReply(['keyboard' => [['Да', 'Нет']]]),
                ]);
                return 'init';
        }

        return null;
    }

    public function gettingAbandoned()
    {
        $this->reply([
            'text' => 'Мы всё ещё отменяем заказ?',
            'reply_markup' => Keyboard::forceReply(['keyboard' => [['Да', 'Нет']]]),
        ]);
    }
}
