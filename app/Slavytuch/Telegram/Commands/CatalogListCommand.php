<?php

namespace App\Slavytuch\Telegram\Commands;

use App\Models\Product;
use App\Slavytuch\Shop\Catalog\CatalogService;
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

        $buttonList = [];
        foreach ($productList as $product) {
            $buttonList[] = [Keyboard::inlineButton(
                ['text' => $product->name, 'callback_data' => 'catalog:display:' . $product->id]
            )];
        }

        if ($productList->hasMorePages()) {
            $buttonList[] = [Keyboard::inlineButton(['text' => 'Следующая страница ->', 'callback_data' => 'catalog:list:2'])];
        }

        $this->replyWithMessage([
            'text' => 'Главная раздела каталога',
            'reply_markup' => Keyboard::make([
                'inline_keyboard' => $buttonList
            ])
        ]);
    }
}
