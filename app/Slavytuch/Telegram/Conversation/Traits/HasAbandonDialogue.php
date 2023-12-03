<?php

namespace App\Slavytuch\Telegram\Conversation\Traits;

use Telegram\Bot\Keyboard\Keyboard;

trait HasAbandonDialogue
{
    abstract function getAbandonPrompt(): string;

    abstract function getAbandonPromptSuccess(): string;

    abstract function reply(array $params);

    public function gettingAbandoned()
    {
        $this->reply([
            'text' => $this->getAbandonPrompt(),
            'reply_markup' => Keyboard::forceReply([
                'keyboard' => [['Да', 'Нет']],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ])
        ]);
    }

    public function abandonSuccess()
    {
        $this->reply([
            'text' => $this->getAbandonPromptSuccess(),
            'reply_markup' => Keyboard::remove(),
        ]);
    }
}
