<?php

namespace App\Slavytuch\Telegram\Util;

use App\Models\User;

class UserHelper
{
    public static function getUserByTelegramId(string $telegramId): ?User
    {
        return User::where('telegram_id', $telegramId)->first();
    }
}
