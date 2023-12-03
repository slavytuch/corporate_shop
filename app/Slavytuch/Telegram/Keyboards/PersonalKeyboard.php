<?php

namespace App\Slavytuch\Telegram\Keyboards;

use App\Slavytuch\Shop\Global\Enums\UserPermissions;
use App\Slavytuch\Shop\Global\Enums\UserRole;
use App\Slavytuch\Telegram\Keyboards\Abstracts\BaseKeyboardAbstract;
use Telegram\Bot\Keyboard\Keyboard;

class PersonalKeyboard extends BaseKeyboardAbstract
{

    public function getKeyboard(): Keyboard
    {
        $keyboard = [
            'inline_keyboard' => [
                [Keyboard::inlineButton(['text' => 'Посмотреть заказы', 'callback_data' => 'personal:order-list'])],
                [Keyboard::inlineButton(['text' => 'Перекинуть денег', 'callback_data' => 'personal:give-currency'])],
                [Keyboard::inlineButton(['text' => 'Поменять имя', 'callback_data' => 'personal:change-name'])]
            ]
        ];

        if ($this->user->can(UserPermissions::SET_MANAGER->value)) {
            $keyboard['inline_keyboard'][] = [
                Keyboard::inlineButton(
                    ['text' => 'Назначить менеджера', 'callback_data' => 'personal:set-manager']
                )
            ];
        }

        if ($this->user->can(UserPermissions::ACCESS_ALL_ORDERS->value)) {
            $keyboard['inline_keyboard'][] = [
                Keyboard::inlineButton(
                    ['text' => 'Список заказов компании', 'callback_data' => 'personal:all-orders']
                )
            ];
        }

        return Keyboard::make($keyboard);
    }
}
