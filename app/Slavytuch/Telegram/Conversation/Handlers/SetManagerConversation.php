<?php

namespace App\Slavytuch\Telegram\Conversation\Handlers;

use App\Models\Order;
use App\Models\User;
use App\Slavytuch\Shop\Global\Enums\UserRole;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Shop\Order\Exceptions\OrderServiceException;
use App\Slavytuch\Shop\Order\OrderService;
use App\Slavytuch\Telegram\Conversation\Abstracts\BaseConversationAbstract;
use Telegram\Bot\Keyboard\Keyboard;

class SetManagerConversation extends BaseConversationAbstract
{
    /**
     * Спрашиваем у пользователя хочет ли он отменить заказ
     */
    public function init(): ?string
    {
        $this->reply(
            [
                'text' => 'Кого назначаем? Напиши ник, id в тг или ссыль с t.me',
            ]
        );

        return 'check';
    }

    public function check()
    {
        $lastStep = $this->history->where('last_stage', 'init')->first();

        $targetUserTelegramUsername = trim(
            str_replace(['https://t.me/', '@'], '', $this->telegram->getWebhookUpdate()->getMessage()->text)
        );
        $targetUser = User::where('telegram_username', $targetUserTelegramUsername)->orWhere(
            'telegram_id',
            $targetUserTelegramUsername
        )->first();

        if (!$targetUser) {
            $this->reply([
                'text' => 'К сожалению, я такого у себя не нашёл, попроси его написать мне что-нибудь, чтобы он появился'
            ]);
            return null;
        }

        if ($targetUser->hasRole(UserRole::MANAGER->value)) {
            $this->reply(['text' => 'Пользователь уже является менеджером']);

            return null;
        }

        $lastStep->response = $targetUser->id;
        $lastStep->save();

        $this->reply([
            'text' => 'Назначаем менеджером пользователя ' . $targetUserTelegramUsername . '?',
            'reply_markup' => Keyboard::make([
                'keyboard' => [['Да', 'Нет']],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);

        return 'confirm';
    }

    /**
     * Уточняем
     */
    public function confirm()
    {
        $confirm = $this->telegram->getWebhookUpdate()->getMessage()->text;

        switch ($confirm) {
            case 'Да':
                $user = $this->history->where('last_stage', 'init')->first();
                $targetUser = User::find($user->response);
                $targetUser->assignRole(UserRole::MANAGER->value);

                $this->reply(
                    [
                        'text' => 'Пользователь ' . $targetUser->telegram_username . ' стал почётным менеджером бота!',
                        'reply_markup' => Keyboard::remove()
                    ]
                );
                break;
            case 'Нет':
                $this->reply(['text' => 'Хорошо', 'reply_markup' => Keyboard::remove()]);
                break;
        }

        return null;
    }
}
