<?php

namespace App\Events;

use App\Models\Balance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BalanceUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Balance $balance,
        public readonly int $oldValue
    ) {
    }
}
