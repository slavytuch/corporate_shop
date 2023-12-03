<?php

namespace App\Slavytuch\Telegram\Conversation\Handlers;

use App\Slavytuch\Telegram\Conversation\Abstracts\BaseConversationAbstract;
use App\Slavytuch\Telegram\Conversation\Abstracts\HasAbandonDialogueInterface;
use App\Slavytuch\Telegram\Conversation\Traits\HasAbandonDialogue;
use Telegram\Bot\Keyboard\Keyboard;

class CreateProductConversation extends BaseConversationAbstract implements HasAbandonDialogueInterface
{
    use HasAbandonDialogue;

    /**
     * Спрашиваем у пользователя как к нему обращаться
     */
    public function init(): ?string
    {
        $this->reply(
            [
                'text' => 'Добвляем товар, первый шаг - название. Для отмены - напиши "Отмена" для сброса',
            ]
        );

        return 'name';
    }

    public function name()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage()->text;

        if (mb_strtolower($message) === 'отмена') {
            $this->reply([
                'text' => 'Отменил',
                'reply_markup' => Keyboard::remove(),
            ]);
            return null;
        }

        $this->reply([
            'text' => 'Принято, теперь - описание. Если его нет - напиши "Нет"'
        ]);

        return 'description';
    }

    public function description()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage()->text;

        if (mb_strtolower($message) === 'отмена') {
            $this->reply([
                'text' => 'Отменил',
                'reply_markup' => Keyboard::remove(),
            ]);
            return null;
        }

        $this->reply(['text' => 'Принято, следующий шаг - картинка']);

        return 'picture';
    }

    public function picture()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage()->text;

        if (mb_strtolower($message) === 'отмена') {
            $this->reply([
                'text' => 'Отменил',
                'reply_markup' => Keyboard::remove(),
            ]);
            return null;
        }

        $this->reply([
            'text' => 'Отлично, осталось самое сложное - цены. Введи цену в формате: ' .
                PHP_EOL . '<Название цены 1> - <Цена 1>' . PHP_EOL . '<Название цены 2> - <Цена 2>'
        ]);

        return 'prices';
    }

    public function prices()
    {

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
                $this->reply(['text' => 'Хорошо, оставляем ' . $this->user->name, 'reply_markup' => Keyboard::remove()]
                );
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
