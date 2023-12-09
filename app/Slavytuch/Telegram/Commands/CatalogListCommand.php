<?php

namespace App\Slavytuch\Telegram\Commands;

use App\Models\Product;
use App\Slavytuch\Shop\Catalog\CatalogService;
use App\Slavytuch\Telegram\Keyboards\CatalogKeyboard;
use App\Slavytuch\Telegram\Util\UserHelper;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class CatalogListCommand extends Command
{
    protected string $name = 'cataloglist';
    protected string $description = 'Раздел каталога';

    public function handle()
    {
        $productList = Product::orderBy('updated_at')->paginate(app(CatalogService::class)->itemsPerPage());

        $this->replyWithMessage([
            'text' => 'Главная раздела каталога',
            'reply_markup' => (new CatalogKeyboard(
                UserHelper::getUserByTelegramId($this->update->getMessage()->from->id),
                $productList
            ))->getKeyboard()
        ]);
    }
}
