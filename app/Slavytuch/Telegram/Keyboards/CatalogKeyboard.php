<?php

namespace App\Slavytuch\Telegram\Keyboards;

use App\Slavytuch\Telegram\Keyboards\Abstracts\BaseKeyboardAbstract;
use Telegram\Bot\Keyboard\Keyboard;

class CatalogKeyboard extends BaseKeyboardAbstract
{

    public function getKeyboard(): Keyboard
    {
        return Keyboard::make([

        ]);
    }
}
