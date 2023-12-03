<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Catalog;

use App\Models\Product;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Telegram\Conversation\ConversationService;
use App\Slavytuch\Telegram\Conversation\Enums\Topic;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Util\UserHelper;

class Buy extends BaseInlineActionAbstract
{

    public function process()
    {
        $parts = explode(':', $this->relatedObject->data);
        $productId = $parts[2];
        $priceTypeId = $parts[4];

        $user = UserHelper::getUserByTelegramId($this->relatedObject->from->id);

        $balance = $user->balances()->where('price_type_id', $priceTypeId)->first();

        $product = Product::find($productId);

        $productPrice = $product->prices()->where('id', $priceTypeId)->first();
        $price = $productPrice->pivot->price;

        if (!$balance || $price > $balance->amount) {
            $this->answer('Не хватает "' . $productPrice->name . '" для покупки :(', true);
            return;
        }

        $conversationService = app(ConversationService::class);

        $conversation = $conversationService->createConversation(
            $this->user,
            Topic::CREATE_ORDER,
            json_encode(['productId' => $productId, 'priceTypeId' => $priceTypeId])
        );

        $conversationService->proceedConversation($this->telegram, $conversation);
        $this->answer();
    }
}
