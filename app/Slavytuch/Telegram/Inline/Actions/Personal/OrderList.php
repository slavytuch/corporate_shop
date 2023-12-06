<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Personal;

use App\Models\User;
use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionFunction;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionProcedure;
use App\Slavytuch\Telegram\Util\UserHelper;
use Illuminate\Support\Collection;
use Telegram\Bot\Keyboard\Keyboard;

class OrderList extends BaseInlineActionAbstract
{
    public function process()
    {
        $user = UserHelper::getUserByTelegramId($this->relatedObject->from->id);

        $orderList = $user->orders()->get();

        if ($orderList->isEmpty()) {
            $this->answer('Заказов пока нет', true);
            return;
        }

        $keyboard = [];
        foreach ($orderList as $order) {
            $text = 'Заказ №' . $order->id . PHP_EOL;
            $items = $order->items()->get();
            foreach ($items as $item) {
                $text .= $item->name . ' - ' . $item->price . ' ' . $item->priceType()->first()->name . PHP_EOL;
            }
            $keyboard[] = [
                Keyboard::inlineButton(
                    ['text' => $text, 'callback_data' => ActionFunction::DISPLAY_ORDER->value . $order->id]
                )
            ];
        }

        $this->telegram->editMessageText([
            'message_id' => $this->relatedObject->message->messageId,
            'chat_id' => $this->relatedObject->message->chat->id,
            'text' => 'Список заказов:',
            'reply_markup' => Keyboard::make(['inline_keyboard' => $keyboard])
        ]);

        $this->answer();
    }
}
