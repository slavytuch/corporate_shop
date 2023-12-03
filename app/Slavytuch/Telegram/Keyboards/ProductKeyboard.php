<?php

namespace App\Slavytuch\Telegram\Keyboards;

use App\Slavytuch\Telegram\Keyboards\Abstracts\BaseKeyboardAbstract;
use App\Slavytuch\Telegram\Keyboards\Abstracts\KeyboardInterface;
use Telegram\Bot\Keyboard\Keyboard;

class ProductKeyboard extends BaseKeyboardAbstract
{
    public function getKeyboard(): Keyboard
    {
        $keyboard = [''];

        return Keyboard::make($keyboard);
    }
}
