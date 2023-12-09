<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Catalog;

use App\Models\Product;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Telegram\Conversation\ConversationService;
use App\Slavytuch\Telegram\Conversation\Enums\Topic;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Util\UserHelper;

class AddProduct extends BaseInlineActionAbstract
{

    public function process()
    {
        $conversationService = app(ConversationService::class);

        $conversation = $conversationService->createConversation(
            $this->user,
            Topic::ADD_PRODUCT,
        );

        $conversationService->proceedConversation($this->telegram, $conversation);
        $this->answer();
    }
}
