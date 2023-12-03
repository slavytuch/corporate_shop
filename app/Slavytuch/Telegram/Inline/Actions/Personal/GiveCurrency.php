<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Personal;

use App\Models\Order;
use App\Models\User;
use App\Slavytuch\Telegram\Conversation\ConversationService;
use App\Slavytuch\Telegram\Conversation\Enums\Topic;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Util\UserHelper;
use Illuminate\Support\Collection;
use Telegram\Bot\Keyboard\Keyboard;

class GiveCurrency extends BaseInlineActionAbstract
{
    public function process()
    {
        $conversationService = app(ConversationService::class);
        $conversation = $conversationService->createConversation($this->user, Topic::GIVE_CURRENCY);

        $conversationService->proceedConversation($this->telegram, $conversation);
        $this->answer();
    }
}
