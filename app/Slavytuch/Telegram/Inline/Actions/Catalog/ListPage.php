<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Catalog;

use App\Models\Product;
use App\Slavytuch\Shop\Catalog\CatalogService;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionFunction;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class ListPage extends BaseInlineActionAbstract
{

    public function process()
    {
        $parts = explode(':', $this->relatedObject->data);
        $page = $parts[array_key_last($parts)];

        $productList = Product::orderBy('updated_at')->paginate(
            app(CatalogService::class)->itemsPerPage(),
            ['*'],
            'page',
            $page
        );

        $buttonList = [];
        foreach ($productList as $product) {
            $buttonList[] = [
                Keyboard::inlineButton(
                    ['text' => $product->name, 'callback_data' => ActionFunction::CATALOG_DISPLAY->value . $product->id]
                )
            ];
        }

        $navigationButtons = [];
        if ($page > 1) {
            $navigationButtons[] =
                Keyboard::inlineButton(
                    ['text' => '<- Предыдущая страница', 'callback_data' => ActionFunction::CATALOG_LIST->value . ($page - 1)]
                );
        }

        if ($productList->hasMorePages()) {
            /** @noinspection PhpWrongStringConcatenationInspection */
            $navigationButtons[] =
                Keyboard::inlineButton(
                    ['text' => 'Следующая страница ->', 'callback_data' =>  ActionFunction::CATALOG_LIST->value . ($page + 1)]
                );
        }

        if (!empty($navigationButtons)) {
            $buttonList[] = $navigationButtons;
        }

        $this->telegram->editMessageText([
            'chat_id' => $this->relatedObject->message->chat->id,
            'message_id' => $this->relatedObject->message->messageId,
            'text' => 'Страница ' . $page,
            'reply_markup' => Keyboard::make([
                'inline_keyboard' => $buttonList
            ])
        ]);
    }
}
