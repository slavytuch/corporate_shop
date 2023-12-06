<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Catalog;

use App\Models\Product;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionFunction;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class DisplayProduct extends BaseInlineActionAbstract
{

    public function process()
    {
        $parts = explode(':', $this->relatedObject->data);

        $mainItemId = $parts[array_key_last($parts)];

        $mainItem = Product::find($mainItemId);

        $caption = $mainItem->name . PHP_EOL . PHP_EOL;

        $priceList = $mainItem->prices()->get();
        foreach ($priceList as $price) {
            $caption .= $price->name . ': ' . $price->pivot->price . PHP_EOL;
        }

        if ($mainItem->description) {
            $caption .= PHP_EOL . $mainItem->description;
        }

        $buyButtonList = [];
        foreach ($priceList as $price) {
            $buyButtonList[] = Keyboard::inlineButton(
                [
                    'text' => 'Купить за ' . $price->name,
                    'callback_data' => ActionFunction::CATALOG_BUY->value . $mainItem->id . ':priceType:' . $price->id
                ]
            );
        }

        $this->telegram->sendPhoto([
            'chat_id' => $this->relatedObject->message->chat->id,
            'photo' => InputFile::create($mainItem->picture),
            'caption' => $caption,
            'reply_markup' => Keyboard::make(
                [
                    'inline_keyboard' => [
                        $buyButtonList,
                        [Keyboard::inlineButton(['text' => 'На главную', 'callback_data' => 'catalog:main'])],
                    ]
                ]
            )
        ]);

        $this->answer();
    }
}
