<?php

namespace App\Slavytuch\Telegram\Keyboards;

use App\Slavytuch\Shop\Global\Enums\UserPermissions;
use App\Slavytuch\Shop\Global\Enums\UserRole;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionProcedure;
use App\Slavytuch\Telegram\Keyboards\Abstracts\BaseKeyboardAbstract;
use Telegram\Bot\Keyboard\Keyboard;

class PersonalKeyboard extends BaseKeyboardAbstract
{

    public function getKeyboard(): Keyboard
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    Keyboard::inlineButton(
                        ['text' => 'Посмотреть заказы', 'callback_data' => ActionProcedure::ORDER_LIST->value]
                    )
                ],
                [
                    Keyboard::inlineButton(
                        ['text' => 'Перекинуть денег', 'callback_data' => ActionProcedure::GIVE_CURRENCY->value]
                    )
                ],
                [
                    Keyboard::inlineButton(
                        ['text' => 'Поменять имя', 'callback_data' => ActionProcedure::CHANGE_NAME->value]
                    )
                ]
            ]
        ];

        if ($this->user->can(UserPermissions::SET_MANAGER->value)) {
            $keyboard['inline_keyboard'][] = [
                Keyboard::inlineButton(
                    ['text' => 'Назначить менеджера', 'callback_data' => ActionProcedure::SET_MANAGER->value]
                )
            ];
        }

        if ($this->user->can(UserPermissions::ACCESS_ALL_ORDERS->value)) {
            $keyboard['inline_keyboard'][] = [
                Keyboard::inlineButton(
                    ['text' => 'Список заказов компании', 'callback_data' => ActionProcedure::ALL_ORDERS->value]
                )
            ];
        }

        return Keyboard::make($keyboard);
    }
}
