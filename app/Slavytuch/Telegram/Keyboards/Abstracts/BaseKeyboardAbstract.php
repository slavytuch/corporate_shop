<?php

namespace App\Slavytuch\Telegram\Keyboards\Abstracts;

use App\Models\User;

abstract class BaseKeyboardAbstract implements KeyboardInterface
{
    public function __construct(protected readonly User $user)
    {
    }
}
