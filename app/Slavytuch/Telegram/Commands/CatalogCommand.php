<?php

namespace App\Slavytuch\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Button;
use Telegram\Bot\Keyboard\Keyboard;

class CatalogCommand extends Command
{
    protected string $name = 'catalog';
    protected string $description = 'Главная каталога';

    public function handle()
    {
        $this->replyWithMessage([
            'text' => 'Каталог',
            'reply_markup' => Keyboard::make(
                [
                    'inline_keyboard' => [
                        [Keyboard::inlineButton(['text' => 'Главная', 'callback_data' => 'catalog:main'])],
                        [Keyboard::inlineButton(['text' => 'Поиск по каталогу', 'callback_data' => 'catalog:search'])]
                    ]
                ]
            )
        ]);
    }
}
