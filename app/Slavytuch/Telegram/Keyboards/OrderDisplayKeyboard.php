<?php

namespace App\Slavytuch\Telegram\Keyboards;

use App\Models\Order;
use App\Slavytuch\Shop\Global\Enums\UserPermissions;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionFunction;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionProcedure;
use App\Slavytuch\Telegram\Keyboards\Abstracts\BaseKeyboardAbstract;
use Telegram\Bot\Keyboard\Keyboard;

class OrderDisplayKeyboard extends BaseKeyboardAbstract
{
    public function __construct(protected Order $order)
    {
        parent::__construct($this->order->user()->first());
    }

    public function getKeyboard(): Keyboard
    {
        $keyboard = [];
        switch ($this->order->status) {
            case Status::CREATED:
                $keyboard[] = [Keyboard::inlineButton(
                    [
                        'text' => 'Отменить заказ',
                        'callback_data' => ActionFunction::CANCEL_ORDER->value . $this->order->id
                    ]
                )];
                break;
        }

        if ($this->user->can(UserPermissions::ACCESS_ALL_ORDERS->value)) {
            switch ($this->order->status) {
                case Status::PROCESSING:
                    $keyboard[] = [Keyboard::inlineButton([
                        'text' => 'Заказ готов!',
                        'callback_data' => ActionFunction::ORDER_READY->value . $this->order->id
                    ])];
                    break;
                case Status::CREATED:
                    $keyboard[] = [Keyboard::inlineButton([
                        'text' => 'Взять в работу',
                        'callback_data' => ActionFunction::ORDER_PROCESSING->value . $this->order->id
                    ])];
                    break;
                case Status::READY:
                    $keyboard[] = [Keyboard::inlineButton([
                        'text' => 'Заказ выдан',
                        'callback_data' => ActionFunction::ORDER_FINISHED->value . $this->order->id
                    ])];
                    break;
            }
        }

        $keyboard[] = [Keyboard::inlineButton(
            ['text' => 'К списку заказов', 'callback_data' => ActionProcedure::ORDER_LIST->value]
        )];

        return Keyboard::make(['inline_keyboard' => $keyboard]);
    }
}
