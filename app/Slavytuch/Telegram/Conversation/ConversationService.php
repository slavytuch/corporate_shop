<?php

namespace App\Slavytuch\Telegram\Conversation;

use App\Models\Conversation;
use App\Models\ConversationHistory;
use App\Models\User;
use App\Slavytuch\Telegram\Conversation\Abstracts\BaseConversationAbstract;
use App\Slavytuch\Telegram\Conversation\Abstracts\HasAbandonDialogueInterface;
use App\Slavytuch\Telegram\Conversation\Enums\Status;
use App\Slavytuch\Telegram\Conversation\Enums\Topic;
use App\Slavytuch\Telegram\Conversation\Handlers\CancelOrderConversation;
use App\Slavytuch\Telegram\Conversation\Handlers\ChangeNameConversation;
use App\Slavytuch\Telegram\Conversation\Handlers\CreateOrderConversation;
use App\Slavytuch\Telegram\Conversation\Handlers\GiveCurrencyConversation;
use App\Slavytuch\Telegram\Inline\Actions\Personal\GiveCurrency;
use Telegram\Bot\Api;

class ConversationService
{
    protected array $topicConversationDictionary = [
        Topic::CHANGE_NAME->value => ChangeNameConversation::class,
        Topic::CREATE_ORDER->value => CreateOrderConversation::class,
        Topic::CANCEL_ORDER->value => CancelOrderConversation::class,
        Topic::GIVE_CURRENCY->value => GiveCurrencyConversation::class
    ];

    public function __construct()
    {
    }

    public function getUserConversation(User $user): ?Conversation
    {
        return Conversation::where('user_id', $user->id)
            ->whereIn('status', [Status::ACTIVE, Status::GETTING_ABANDONED])
            ->first();
    }

    public function createConversation(User $user, Topic $topic, ?string $intialData = null)
    {
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'topic' => $topic,
            'status' => Status::ACTIVE,
        ]);

        if ($intialData) {
            $conversation->history()->save(
                new ConversationHistory([
                    'response' => $intialData,
                    'next_stage' => 'init'
                ])
            );
        }

        return $conversation;
    }

    public function proceedConversation(Api $telegram, Conversation $conversation): void
    {
        $lastHistoryItem = $conversation->history()->orderBy('id', 'desc')->first();

        /**
         * @var BaseConversationAbstract $handler
         */
        $handler = new ($this->topicConversationDictionary[$conversation->topic->value])($telegram, $conversation);

        $method = $lastHistoryItem?->next_stage ? $lastHistoryItem->next_stage : 'init';

        $handlerResult = $handler->$method();

        $message = $telegram->getWebhookUpdate()->getMessage()->text;

        $conversation->history()->save(
            new ConversationHistory([
                'last_stage' => $method,
                'response' => $message ?: 'no text',
                'next_stage' => $handlerResult
            ])
        );

        if (!$handlerResult) {
            $conversation->status = Status::FINISHED;
            $conversation->save();
        }
    }

    public function conversationGettingAbandoned(Api $telegram, Conversation $conversation): void
    {
        $handler = new ($this->topicConversationDictionary[$conversation->topic->value])($telegram, $conversation);

        if (!class_implements($handler, HasAbandonDialogueInterface::class)) {
            $this->abandonConversation($conversation);
            return;
        }

        $conversation->status = Status::GETTING_ABANDONED;
        $conversation->save();
        /**
         * @var BaseConversationAbstract $handler
         */
        $handler->gettingAbandoned();
    }

    public function abandonConversation(Conversation $conversation): void
    {
        $conversation->status = Status::ABANDONED;
        $conversation->save();
    }
}
