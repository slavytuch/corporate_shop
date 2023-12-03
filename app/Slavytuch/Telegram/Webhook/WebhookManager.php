<?php

namespace App\Slavytuch\Telegram\Webhook;

use App\Models\User;
use App\Slavytuch\Telegram\Conversation\Abstracts\HasAbandonDialogueInterface;
use App\Slavytuch\Telegram\Conversation\ConversationService;
use App\Slavytuch\Telegram\Conversation\Enums\Status;
use App\Slavytuch\Telegram\Inline\Actions\Exceptions\ActionNotFoundException;
use App\Slavytuch\Telegram\Inline\Factories\InlineActionFactory;
use App\Slavytuch\Telegram\Util\UserHelper;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\CallbackQuery;
use Telegram\Bot\Objects\Message;

class WebhookManager
{
    protected User $user;

    public function __construct(
        protected readonly Api $telegram,
        protected LoggerInterface $logger,
        public ConversationService $conversationService
    ) {
    }

    /**
     * Обработка запроса от ТГ
     *
     * @param Request $request
     */
    public function handleRequest(Request $request): void
    {
        $this->logger->info('request', ['request' => $request->toArray()]);
        $update = $this->telegram->getWebhookUpdate();
        $relatedObject = $update->getRelatedObject();

        $from = $update->getMessage()->from;
        $user = null;
        if ($from && !$user = UserHelper::getUserByTelegramId($from->id)) {
            $user = User::create(
                [
                    'telegram_id' => $from->id,
                    'telegram_username' => $from->username,
                    'name' => $from->first_name ?? $from->username,
                    'chat_id' => $update->getMessage()->chat['id']
                ]
            );
        }

        if (!$user) {
            throw new \Exception('Нет пользователя для обработки');
        }

        $openConversation = $this->conversationService->getUserConversation($user);

        switch (get_class($relatedObject)) {
            case CallbackQuery::class:
                $factory = new InlineActionFactory($this->telegram, $relatedObject);
                $action = $factory->getAction();
                $action->process();
                return;
            case Message::class:
                if ($openConversation) {
                    if (
                        str_starts_with($relatedObject->text, '/') &&
                        $openConversation->status !== Status::GETTING_ABANDONED
                    ) {
                        if (class_implements($openConversation, HasAbandonDialogueInterface::class)) {
                            $this->conversationService->conversationGettingAbandoned(
                                $this->telegram,
                                $openConversation
                            );
                            return;
                        }

                        $this->conversationService->abandonConversation($openConversation);
                    }

                    if ($openConversation->status === Status::GETTING_ABANDONED && $relatedObject->text === 'Нет') {
                        $openConversation->
                        $this->conversationService->abandonConversation($openConversation);
                        return;
                    } else {
                        $this->conversationService->proceedConversation($this->telegram, $openConversation);
                        return;
                    }
                }

                break;
        }

        $update = $this->telegram->commandsHandler(true);
        $this->logger->info('webhook', ['update' => $update]);
    }
}
