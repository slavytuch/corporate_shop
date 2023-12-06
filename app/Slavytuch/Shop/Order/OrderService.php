<?php

namespace App\Slavytuch\Shop\Order;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\Product;
use App\Models\User;
use App\Slavytuch\Shop\Order\Enums\Status;
use App\Slavytuch\Shop\Order\Exceptions\OrderServiceException;
use App\Slavytuch\Shop\Order\Utils\OrderLang;

class OrderService
{
    public function createOrder(User $user, Product $product, ?PriceType $priceType = null): Order
    {
        $balance = $user->balances()->where('price_type_id', $priceType->id)->first();

        $price = $product->prices()->where('id', $priceType->id)->first()->pivot->price;

        if (!$balance || $price > $balance->amount) {
            throw new \Exception('Не хватает денег для покупки');
        }

        $order = $user->orders()->create([
            'status' => Status::CREATED
        ]);

        $order->items()->create([
            'name' => $product->name,
            'price' => $price,
            'price_type_id' => $priceType->id,
            'count' => 1,
        ]);

        $balance->amount -= $price;
        $balance->save();

        OrderCreated::dispatch($order);

        return $order;
    }

    public function startProcessing(Order $order)
    {
        if ($order->status !== Status::CREATED) {
            throw new OrderServiceException('Заказ уже обрабатывается');
        }

        $order->status = Status::PROCESSING;
        $order->save();

        OrderStatusChanged::dispatch($order, Status::CREATED);
    }

    /**
     * Отмена заказа
     *
     * @param Order $order
     * @param bool $dispatchEvent
     * @throws OrderServiceException
     */
    public function cancelOrder(Order $order, bool $dispatchEvent = true)
    {
        $this->checkCancel($order);

        $balances = $order->user->balances()->get();

        foreach ($order->items()->get() as $item) {
            $balance = $balances->where('price_type_id', $item->price_type_id)->first();
            $balance->amount += $item->price;
            $balance->save();
        }

        $oldStatus = $order->status;
        $order->status = Status::CANCELLED;
        $order->save();

        if ($dispatchEvent) {
            OrderStatusChanged::dispatch($order, $oldStatus);
        }
    }

    /**
     * Проверка что заказ можно отменить. При ошибке вызывает исключение
     *
     * @param Order $order
     * @throws OrderServiceException
     */
    public function checkCancel(Order $order)
    {
        if ($order->status === Status::CANCELLED) {
            throw new OrderServiceException('Заказ уже отменён');
        }

        if ($order->status === Status::FINISHED) {
            throw new OrderServiceException('Заказ уже выдан');
        }
    }

    /**
     * Формирует текстовое представление о заказе
     *
     * @param Order $order
     * @return string
     */
    public function makeOrderDisplayText(Order $order): string
    {
        $text = 'Заказ №' . $order->id . PHP_EOL . PHP_EOL .
            'Статус: ' . OrderLang::getStatusName($order->status) . PHP_EOL .
            'Состав:' . PHP_EOL;

        foreach ($order->items()->get() as $item) {
            $itemText = $item->name . ' - ' . $item->price . ' ' . $item->priceType()->first()->name . PHP_EOL;
            $text .= $itemText;
        }

        return $text;
    }

    public function finishOrder(Order $order)
    {
        if ($order->status !== Status::READY) {
            throw new OrderServiceException('Заказ не готов');
        }

        $oldStatus = $order->status;

        $order->status = Status::FINISHED;
        $order->save();

        OrderStatusChanged::dispatch($order, $oldStatus);
    }

    public function setOrderReady(Order $order)
    {
        if (!in_array($order->status, [Status::CREATED, Status::PROCESSING])) {
            throw new OrderServiceException('Заказ уже завершён или отменён');
        }
        $oldStatus = $order->status;

        $order->status = Status::READY;
        $order->save();

        OrderStatusChanged::dispatch($order, $oldStatus);
    }
}
