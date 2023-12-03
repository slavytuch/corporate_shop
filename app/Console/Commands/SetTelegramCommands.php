<?php

namespace App\Console\Commands;

use App\Slavytuch\Telegram\Commands\CatalogItemCommand;
use App\Slavytuch\Telegram\Commands\CatalogListCommand;
use App\Slavytuch\Telegram\Commands\GreetCommand;
use App\Slavytuch\Telegram\Commands\PersonalCommand;
use Illuminate\Console\Command;
use Telegram\Bot\Api;

class SetTelegramCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-telegram-commands';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Установить левое меню телеграмма';

    /**
     * Execute the console command.
     */
    public function handle(Api $telegram)
    {
        $telegram->setMyCommands([
            'commands' => [
                [
                    'command' => app(PersonalCommand::class)->getName(),
                    'description' => 'Личный кабинет'
                ],
                [
                    'command' => app(CatalogListCommand::class)->getName(),
                    'description' => 'Каталог'
                ],
                [
                    'command' => app(GreetCommand::class)->getName(),
                    'description' => 'Приветствие'
                ],
            ]
        ]);
    }
}
