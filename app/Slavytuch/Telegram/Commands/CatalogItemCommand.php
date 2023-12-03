<?php

namespace App\Slavytuch\Telegram\Commands;

use App\Models\Product;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class CatalogItemCommand extends Command
{
    protected string $name = 'catalogitem';
    protected string $description = 'Товар каталога';

    public function handle()
    {
        $productList = Product::all();

        $mainItem = $productList->first();

        $caption = $mainItem->name . PHP_EOL .  PHP_EOL;

        foreach ($mainItem->prices()->get() as $price) {
            $caption .= $price->name . ': ' . $price->pivot->price . PHP_EOL;
        }

        if($mainItem->description) {
            $caption .= PHP_EOL . $mainItem->description;
        }

        $nextItem = $productList->offsetGet(1);
        $previousItem = $productList->last();

        $this->replyWithPhoto([
            'photo' => InputFile::create($mainItem->picture),
            'caption' => $caption,
            'reply_markup' => Keyboard::make(
                [
                    'inline_keyboard' => [
                        [
                            Keyboard::inlineButton(['text' => '<- Предыдущий', 'callback_data' => 'catalog:display:' . $previousItem->id]),
                            Keyboard::inlineButton(['text' => 'Следующий ->', 'callback_data' => 'catalog:display:' . $nextItem->id])
                        ],
                        [Keyboard::inlineButton(['text' => 'Купить', 'callback_data' => 'catalog:buy:' . $mainItem->id])],
                        [Keyboard::inlineButton(['text' => 'На главную', 'callback_data' => 'catalog:main'])],
                    ]
                ]
            )
        ]);
    }
}
