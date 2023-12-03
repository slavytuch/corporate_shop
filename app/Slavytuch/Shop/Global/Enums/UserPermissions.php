<?php

namespace App\Slavytuch\Shop\Global\Enums;

enum UserPermissions: string
{
    case SET_MANAGER = 'set-manager';
    case ACCESS_ALL_ORDERS = 'access-all-orders';
    case ACCESS_CATALOG = 'access-catalog';
}
