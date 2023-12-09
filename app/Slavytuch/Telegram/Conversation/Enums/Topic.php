<?php

namespace App\Slavytuch\Telegram\Conversation\Enums;

enum Topic: string
{
    case CHANGE_NAME = 'change-name';
    case CREATE_ORDER = 'create-order';
    case CANCEL_ORDER = 'cancel-order';
    case GIVE_CURRENCY = 'give-currency';
    case SET_MANAGER = 'set-manager';
    case ADD_PRODUCT = 'add-product';
}
