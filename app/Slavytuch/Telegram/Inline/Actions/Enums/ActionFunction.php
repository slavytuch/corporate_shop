<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Enums;

enum ActionFunction: string
{
    case CATALOG_DISPLAY = 'catalog:display:';
    case CATALOG_LIST = 'catalog:list:';
    case CATALOG_BUY = 'catalog:buy:';
    case DISPLAY_TEXT = 'display-text:';
    case DISPLAY_ORDER = 'personal:display-order:';
    case CANCEL_ORDER = 'personal:cancel-order:';
    case MANAGER_CANCEL_ORDER = 'personal:manager-cancel-order:';
    case ORDER_PROCESSING = 'personal:order-processing:';
    case ORDER_READY = 'personal:order-ready:';
    case ORDER_FINISHED = 'personal:order-finished:';
}
