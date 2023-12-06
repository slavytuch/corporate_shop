<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Catalog;

use App\Models\Product;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionFunction;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionProcedure;
use App\Slavytuch\Telegram\Keyboards\ProductKeyboard;
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

        $this->telegram->sendPhoto([
            'chat_id' => $this->relatedObject->message->chat->id,
            'photo' => InputFile::create($mainItem->picture),
            'caption' => $caption,
            'reply_markup' => (new ProductKeyboard($this->user, $mainItem))->getKeyboard()
        ]);

        $this->answer();
    }
}
