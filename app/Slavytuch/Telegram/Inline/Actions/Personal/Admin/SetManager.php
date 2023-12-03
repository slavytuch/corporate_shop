<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Personal\Admin;

use App\Models\Order;
use App\Models\User;
use App\Slavytuch\Telegram\Conversation\ConversationService;
use App\Slavytuch\Telegram\Conversation\Enums\Topic;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Util\UserHelper;
use Illuminate\Support\Collection;
use Telegram\Bot\Keyboard\Keyboard;

class SetManager extends BaseInlineActionAbstract
{
    public function process()
    {
        $parts = explode(':', $this->relatedObject->data);

        $orderId = $parts[array_key_last($parts)];

        $conversationService = app(ConversationService::class);

        $conversation = $conversationService->createConversation($this->user, Topic::CANCEL_ORDER, $orderId);

        $conversationService->proceedConversation($this->telegram, $conversation);
        $this->answer();
    }
}
