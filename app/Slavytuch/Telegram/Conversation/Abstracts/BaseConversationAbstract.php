<?php

namespace App\Slavytuch\Telegram\Conversation\Abstracts;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Collection;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Message;

abstract class BaseConversationAbstract
{
    protected Collection $history;
    protected readonly User $user;

    public function __construct(
        protected Api $telegram,
        protected readonly Conversation $conversation,
    ) {
        $this->user = User::find($this->conversation->user_id);
        $this->history = $this->conversation->history()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Первый вызываемый метод, каждый метод цепочки должен вызывать название следующего метода для обработки
     */
    abstract public function init(): ?string;

    /**
     * @param array $params
     * @throws TelegramSDKException
     */
    protected function reply(array $params)
    {
        $this->telegram->sendMessage(
            array_merge(['chat_id' => $this->telegram->getWebhookUpdate()->getChat()->get('id')], $params)
        );
    }
}
