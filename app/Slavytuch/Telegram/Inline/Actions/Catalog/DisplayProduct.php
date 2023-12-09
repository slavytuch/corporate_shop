<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Catalog;

use App\Models\Product;
use App\Slavytuch\Shop\Catalog\CatalogService;
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

        $productId = $parts[array_key_last($parts)];

        try {
            $preparedProduct = app(CatalogService::class)->prepareProductDisplay($productId);
        } catch (\Exception $ex) {
            $this->answer($ex->getMessage());
            return;
        }

        if ($preparedProduct->picture) {
            $this->telegram->sendPhoto([
                'chat_id' => $this->relatedObject->message->chat->id,
                'photo' => str_contains($preparedProduct->picture, public_path()) ? InputFile::create($preparedProduct->picture) : $preparedProduct->picture,
                'caption' => $preparedProduct->caption,
                'reply_markup' => (new ProductKeyboard($this->user, $preparedProduct->product))->getKeyboard()
            ]);
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $this->relatedObject->message->chat->id,
                'text' => $preparedProduct->caption,
                'reply_markup' => (new ProductKeyboard($this->user, $preparedProduct->product))->getKeyboard()
            ]);
        }

        $this->answer();
    }
}
