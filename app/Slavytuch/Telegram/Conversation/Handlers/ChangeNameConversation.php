<?php

namespace App\Slavytuch\Telegram\Conversation\Handlers;

use App\Slavytuch\Telegram\Conversation\Abstracts\BaseConversationAbstract;
use App\Slavytuch\Telegram\Conversation\Abstracts\HasAbandonDialogueInterface;
use App\Slavytuch\Telegram\Conversation\Traits\HasAbandonDialogue;
use Telegram\Bot\Keyboard\Keyboard;

class ChangeNameConversation extends BaseConversationAbstract implements HasAbandonDialogueInterface
{
    use HasAbandonDialogue;

    /**
     * Спрашиваем у пользователя как к нему обращаться
     */
    public function init(): ?string
    {
        $this->reply(
            [
                'text' => 'Как мне к тебе обращаться? Сейчас твоё имя - ' . $this->user->name,
            ]
        );

        return 'prompt';
    }

    /**
     * Уточняем
     */
    public function prompt()
    {
        $newName = $this->telegram->getWebhookUpdate()->getMessage()->text;

        $this->reply(
            [
                'text' => 'Поменять твоё имя на ' . $newName . '?',
                'reply_markup' => Keyboard::forceReply([
                    'keyboard' => [['Да', 'Нет'], ['Не совсем']],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]
        );

        return 'change';
    }

    /**
     * Меняем, или нет
     */
    public function change()
    {
        $confirm = $this->telegram->getWebhookUpdate()->getMessage()->text;
        switch ($confirm) {
            case 'Да':
                $newName = $this->history->where('next_stage', 'change')->first()?->response;
                $this->user->name = $newName;
                $this->user->save();
                $this->reply(['text' => 'Да здравствует ' . $newName . '!', 'reply_markup' => Keyboard::remove()]);
                break;
            case 'Нет':
                $this->reply(['text' => 'Хорошо, оставляем ' . $this->user->name, 'reply_markup' => Keyboard::remove()]);
                break;
            case 'Не совсем':
                $this->reply(['text' => 'Хорошо, тогда как?', 'reply_markup' => Keyboard::remove()]);
                return 'init';
            default:
                $name = $this->history->where('next_stage', 'change')->first()?->response;
                $this->reply([
                    'text' => 'Мы всё ещё меняем имя на ' . $name . '?',
                    'reply_markup' => Keyboard::forceReply([
                        'keyboard' => [['Да', 'Нет']],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ])
                ]);
                return 'prompt';
        }

        return null;
    }

    function getAbandonPrompt(): string
    {
        return 'Мы всё ещё меняем твоё имя?';
    }

    function getAbandonPromptSuccess(): string
    {
        return 'Хорошо, оставляем ' . $this->user->name . '. Пожалуста, введи команду ещё раз чтобы продолжить';
    }
}
