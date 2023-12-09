<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Enums;

enum ActionProcedure: string
{
    case CHANGE_NAME = 'personal:change-name';
    case ORDER_LIST = 'personal:order-list';
    case GIVE_CURRENCY = 'personal:give-currency';
    case SET_MANAGER = 'personal:set-manager';
    case ALL_ORDERS = 'personal:all-orders';
    case ADD_PRODUCT = 'catalog:add-product';
}
