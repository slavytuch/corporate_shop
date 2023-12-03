<?php

namespace App\Slavytuch\Shop\Order\Enums;

enum Status
{
    case CREATED;
    case PROCESSING;
    case READY;
    case FINISHED;
    case CANCELLED;
}
