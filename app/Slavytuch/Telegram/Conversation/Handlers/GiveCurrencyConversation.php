<?php

namespace App\Slavytuch\Telegram\Conversation\Handlers;

use App\Models\PriceType;
use App\Models\User;
use App\Slavytuch\Shop\User\Balance\BalanceService;
use App\Slavytuch\Telegram\Conversation\Abstracts\BaseConversationAbstract;
use Telegram\Bot\Keyboard\Keyboard;

class GiveCurrencyConversation extends BaseConversationAbstract
{
    /**
     * Спрашиваем у пользователя хочет ли он отменить заказ
     */
    public function init(): ?string
    {
        $this->reply(
            [
                'text' => 'Хорошо, кому отправляем? Напиши ник пользователя или закинь его ссылку с https://t.me/<ник>',
            ]
        );

        return 'user';
    }

    public function user()
    {
        $lastStep = $this->history->where('last_stage', 'init')->first();

        $targetUserTelegramUsername = trim(
            str_replace(['https://t.me/', '@'], '', $this->telegram->getWebhookUpdate()->getMessage()->text)
        );

        $targetUser = User::where('telegram_username', $targetUserTelegramUsername)->orWhere('telegram_id', $targetUserTelegramUsername)->first();

        if (!$targetUser) {
            $this->reply([
                'text' => 'К сожалению, я такого у себя не нашёл, попроси его написать мне что-нибудь, чтобы он появился'
            ]);
            return null;
        }

        $lastStep->response = $targetUser->id;
        $lastStep->save();

        $balances = $this->user->balances()->get();

        $balanceText = '';
        $balancesButtons = [];
        foreach ($balances as $balance) {
            $balanceText .= $balance->priceType->name . ' - ' . $balance->amount . PHP_EOL;
            $balancesButtons[] = [$balance->priceType->name];
        }

        $this->reply([
            'text' => 'Принято! Какую валюту будем переводить? Сейчас у тебя:' . PHP_EOL . $balanceText,
            'reply_markup' => Keyboard::make([
                'keyboard' => $balancesButtons,
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);

        return 'type';
    }

    public function type()
    {
        $type = $this->telegram->getWebhookUpdate()->getMessage()->text;

        if (mb_strtolower($type) === 'отмена') {
            $this->reply(['text' => 'Отменил', 'reply_markup' => Keyboard::remove()]);
            return null;
        }

        if (!PriceType::where('name', $type)->first()) {
            $this->reply(
                [
                    'text' => 'У меня такой валюты нет, выбери пожалуста вариант из клавиатуры, или напиши "Отмена"',
                ]
            );
            return 'type';
        }

        $this->reply([
            'text' => 'И осталось самое главное - сколько переводить? Напиши число',
            'reply_markup' => Keyboard::remove(),
        ]);

        return 'confirm';
    }

    public function confirm()
    {
        $responseAmount = $this->telegram->getWebhookUpdate()->getMessage()->text;

        if (mb_strtolower($responseAmount) === 'отмена') {
            $this->reply(['text' => 'Отменил', 'reply_markup' => Keyboard::remove()]);
            return null;
        }

        $amount = abs((int)$responseAmount);

        if (abs((int)$amount) != $responseAmount) {
            $this->reply(
                [
                    'text' => 'Не смог понять сколько нужно. Я понимаю только целые числа, без пробелов. Напиши ещё раз или напиши "Отмена"',
                    'reply_markup' => Keyboard::make([
                        'keyboard' => [['Отмена']],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ])
                ]
            );
            return 'confirm';
        }

        $type = $this->history->where('last_stage', 'type')->first();
        $currentUserBalance = $this->user
            ->balances()
            ->whereRelation('priceType', 'name', $type->response)
            ->first();

        if ($currentUserBalance->amount < $amount) {
            $this->reply(
                [
                    'text' => 'К сожалению, у тебя столько нет. Отправь новое число, или напиши "Отмена"',
                    'reply_markup' => Keyboard::make([
                        'keyboard' => [['Отмена']],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ])
                ]
            );

            return 'confirm';
        }

        $user = $this->history->where('last_stage', 'init')->first();

        $targetUser = User::find($user->response);

        $this->reply([
            'text' => 'Принято, итого переводим пользователю @' .
                $targetUser->telegram_username . ' ' . $amount . ' "' . $type->response . '"' . PHP_EOL . 'Всё верно?',
            'reply_markup' => Keyboard::make([
                'keyboard' => [['Да', 'Нет']],
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ])
        ]);

        return 'send';
    }

    public function send()
    {
        $confirm = $this->telegram->getWebhookUpdate()->getMessage()->text;

        switch ($confirm) {
            case 'Да':
                $user = $this->history->where('last_stage', 'init')->first();
                $targetUser = User::find($user->response);
                $amount = $this->history->where('last_stage', 'confirm')->first()?->response;
                $amount = abs((int)$amount);
                $type = $this->history->where('last_stage', 'type')->first()?->response;
                $priceType = PriceType::where('name', $type)->first();
                app(BalanceService::class)->transferCurrency(
                    $this->user,
                    $targetUser,
                    $amount,
                    $priceType,
                    'Ручной перевод'
                );
                $this->reply(['text' => 'Отправил', 'reply_markup' => Keyboard::remove()]);
                $this->telegram->sendMessage(
                    [
                        'text' => 'Пришёл перевод на ' . $amount . ' ' . $priceType->name . ' от ' . $user->name,
                        'chat_id' => $targetUser->chat_id
                    ]
                );
                return null;
            case 'Нет':
                $this->reply(['text' => 'Отменил', 'reply_markup' => Keyboard::remove()]);
                return null;
            default:
                $this->reply([
                    'text' => 'Не совсем понял, перевод завершаем? Напиши "Да" или "Нет", пожалуста'
                ]);
                return 'send';
        }
    }
}
