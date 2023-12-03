<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Personal;

use App\Slavytuch\Telegram\Conversation\ConversationService;
use App\Slavytuch\Telegram\Conversation\Enums\Topic;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;

class ChangeName extends BaseInlineActionAbstract
{
    public function process()
    {
        $conversationService = app(ConversationService::class);

        $newConversation = $conversationService->createConversation(
            $this->user,
            Topic::CHANGE_NAME
        );

        $conversationService->proceedConversation($this->telegram, $newConversation);
        $this->answer();
    }
}
