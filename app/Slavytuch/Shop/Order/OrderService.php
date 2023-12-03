<?php

namespace App\Slavytuch\Shop\Order;

use App\Models\Order;
use App\Models\PriceType;
use App\Models\Product;
use App\Models\User;
use App\Slavytuch\Shop\Order\Enums\Status;

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

        return $order;
    }
}
