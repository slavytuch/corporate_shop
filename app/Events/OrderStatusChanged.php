<?php

namespace App\Events;

use App\Models\Order;
use App\Slavytuch\Shop\Order\Enums\Status;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Заказ поменялся
     *
     * @param Order $order
     * @param Status $oldValue
     */
    public function __construct(
        public readonly Order $order,
        public readonly Status $oldValue
    ) {
    }
}
