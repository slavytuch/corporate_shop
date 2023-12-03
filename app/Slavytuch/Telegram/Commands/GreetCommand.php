<?php

namespace App\Slavytuch\Telegram\Commands;

use App\Slavytuch\Telegram\Util\UserHelper;
use App\Slavytuch\Telegram\Webhook\WebhookManager;
use Telegram\Bot\Commands\Command;

class GreetCommand extends Command
{
    protected string $name = 'greet';
    protected array $aliases = ['start'];
    protected string $description = 'Приветствует пользователя';

    public function handle()
    {
        $message = $this->getUpdate()->getMessage();

        $name = UserHelper::getUserByTelegramId($message->from->id)?->name ?? $message->from->first_name;

        $replyVariants = [
            'С возвращением, ' . $name,
            'Рад видеть тебя, ' . $name,
            'Здравствуй, ' . $name,
            'Привет, ' . $name,
            'Приветствую, ' . $name,
            'Добро пожаловать, ' . $name,
        ];

        $currentHour = now()->format('H');
        if ($currentHour < 6 || $currentHour > 22) {
            $replyVariants = array_merge($replyVariants, ['Доброй ночи, ' . $name,
                'Не спится, ' . $name . '?']);
        }

        if ($currentHour > 6 && $currentHour < 10) {
           $replyVariants = array_merge($replyVariants, [
               'Доброе утро, ' . $name,
               'Здравствуй, ' . $name . ', ты сегодня рановато :)'
           ]);
        }

        if ($currentHour > 9 && $currentHour < 18) {
            $replyVariants[] = 'Добрый день, ' . $name;
        }

        if ($currentHour > 17 && $currentHour < 23) {
            $replyVariants[] = 'Добрый вечер, ' . $name;
        }

        $this->replyWithMessage([
            'text' => $replyVariants[array_rand($replyVariants)],
        ]);
    }
}
