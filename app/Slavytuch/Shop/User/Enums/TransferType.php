<?php

namespace App\Slavytuch\Shop\User\Enums;

enum TransferType: string
{
    case INCOME = 'income';
    case DEDUCTION = 'deduction';
}
