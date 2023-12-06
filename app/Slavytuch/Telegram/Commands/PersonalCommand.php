<?php

namespace App\Slavytuch\Telegram\Commands;

use App\Slavytuch\Telegram\Keyboards\Enums\KeyboardType;
use App\Slavytuch\Telegram\Keyboards\Factories\KeyboardFactory;
use App\Slavytuch\Telegram\Keyboards\PersonalKeyboard;
use App\Slavytuch\Telegram\Util\UserHelper;
use Telegram\Bot\Commands\Command;

class PersonalCommand extends Command
{
    protected string $name = 'personal';
    protected string $description = 'Личный кабинет';

    public function handle()
    {
        $user = UserHelper::getUserByTelegramId($this->getUpdate()->getMessage()->from->id);

        $balances = $user->balances()->get();

        $text = '';
        foreach ($balances as $balance) {
            $text .= $balance->priceType()->first()->name . ': ' . $balance->amount . PHP_EOL;
        }

        $this->replyWithMessage([
            'text' => $text ?: 'Баланс пуст',
            'reply_markup' => (new PersonalKeyboard($user))->getKeyboard()
        ]);
    }
}
