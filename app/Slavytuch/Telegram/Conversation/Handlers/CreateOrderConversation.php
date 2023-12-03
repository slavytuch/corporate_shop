<?php

namespace App\Slavytuch\Telegram\Conversation\Handlers;

use App\Models\PriceType;
use App\Models\Product;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Shop\Order\OrderService;
use App\Slavytuch\Telegram\Conversation\Abstracts\BaseConversationAbstract;
use App\Slavytuch\Telegram\Conversation\Abstracts\HasAbandonDialogueInterface;
use App\Slavytuch\Telegram\Conversation\Traits\HasAbandonDialogue;
use App\Slavytuch\Telegram\Util\UserHelper;
use Telegram\Bot\Keyboard\Keyboard;

class CreateOrderConversation extends BaseConversationAbstract implements HasAbandonDialogueInterface
{
    use HasAbandonDialogue;

    /**
     * Спрашиваем у пользователя хочет ли он купить товар
     */
    public function init(): ?string
    {
        $data = json_decode($this->history->whereNull('last_stage')->first()->response, true);

        $product = Product::find($data['productId']);

        if (!$product) {
            $this->reply(['text' => 'Не могу найти товар!!']);
        }

        $priceType = PriceType::find($data['priceTypeId']);

        $price = $product->prices()->where('id', $priceType->id)->first()->pivot->price;

        $this->reply(
            [
                'text' => 'Покупаем ' . $product->name . ' за ' . $price . ' "' . $priceType->name . '"?',
                'reply_markup' => Keyboard::forceReply([
                    'keyboard' => [['Да', 'Нет']],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]
        );

        return 'confirm';
    }

    /**
     * Формируем заказ
     */
    public function confirm()
    {
        $confirm = $this->telegram->getWebhookUpdate()->getMessage()->text;
        $data = json_decode($this->history->whereNull('last_stage')->first()->response, true);
        $product = Product::find($data['productId']);

        switch ($confirm) {
            case 'Да':
                $order = app(OrderService::class)->createOrder(
                    $this->user,
                    Product::find($data['productId']),
                    PriceType::find($data['priceTypeId'])
                );
                $this->reply(
                    ['text' => 'Заказ сформирован! Номер заказа - ' . $order->id, 'reply_markup' => Keyboard::remove()]
                );
                break;
            case 'Нет':
                $this->reply(['text' => 'Хорошо', 'reply_markup' => Keyboard::remove()]);
                break;
            default:
                $this->reply([
                    'text' => 'Мы всё ещё покупаем ' . $product->name . '?',
                    'reply_markup' => Keyboard::forceReply([
                        'keyboard' => [['Да', 'Нет']],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]),
                ]);
                return 'init';
        }

        return null;
    }

    public function getAbandonPrompt(): string
    {
        $productId = $this->history->first()->response;

        $product = Product::find($productId);

        if (!$product) {
            throw new \Exception('Не могу найти товар - ' . $productId);
        }

        return 'Мы всё ещё покупаем ' . $product->name . '?';
    }

    public function getAbandonPromptSuccess(): string
    {
        return 'Хорошо. Пожалуста, введи команду ещё раз чтобы продолжить';
    }
}
