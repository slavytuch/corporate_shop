<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Catalog;

use App\Models\Product;
use App\Slavytuch\Shop\Catalog\CatalogService;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Keyboards\CatalogKeyboard;

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

        $this->telegram->editMessageText([
            'chat_id' => $this->relatedObject->message->chat->id,
            'message_id' => $this->relatedObject->message->messageId,
            'text' => 'Страница ' . $page,
            'reply_markup' => (new CatalogKeyboard($this->user, $productList))->getKeyboard()
        ]);
    }
}
