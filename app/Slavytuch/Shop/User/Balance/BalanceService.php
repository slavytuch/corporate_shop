<?php

namespace App\Slavytuch\Shop\User\Balance;

use App\Models\PriceType;
use App\Models\User;
use App\Slavytuch\Shop\User\Enums\TransferType;

class BalanceService
{
    public function __construct()
    {
    }

    public function getUserBalance(User $user)
    {
    }

    public function transferCurrency(User $from, User $to, int $amount, PriceType $priceType, ?string $reason = null)
    {
        $fromBalance = $from->balances()->where('price_type_id', $priceType->id)->first();

        if (!$fromBalance) {
            throw new \Exception('Нет счёта у пользователя, с которого выполняется перевод');
        }

        if ($fromBalance->amount < $amount) {
            throw new \Exception('Не хватает средств для перевода');
        }

        $toBalance = $to->balances()->where('price_type_id', $priceType->id)->first();

        if (!$toBalance) {
            $toBalance = $to->balances()->create(['price_type_id' => $priceType->id]);
        }

        $fromBalance->amount -= $amount;
        $fromBalance->save();
        $fromBalance->history()->create(
            [
                'type' => TransferType::DEDUCTION,
                'value' => $amount,
                'from' => $from->id,
                'to' => $to->id,
                'reason' => $reason
            ]
        );

        $toBalance->amount += $amount;
        $toBalance->save();

        $toBalance->history()->create([
            'type' => TransferType::INCOME,
            'value' => $amount,
            'from' => $from->id,
            'to' => $to->id,
            'reason' => $reason
        ]);
    }
}
