<?php

namespace App\Slavytuch\Telegram\Keyboards\Abstracts;

use Telegram\Bot\Keyboard\Keyboard;

interface KeyboardInterface
{
    public function getKeyboard(): Keyboard;
}
