<?php

namespace App\Slavytuch\Telegram\Keyboards\Enums;

enum KeyboardType: string
{
    case PERSONAL = 'personal';
    case CATALOG = 'catalog';
    case PRODUCT = 'product';
    case ORDER = 'order';
}
