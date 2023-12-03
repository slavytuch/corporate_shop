<?php

namespace App\Slavytuch\Telegram\Conversation\Enums;

enum Status: string
{
    case ACTIVE = 'active';
    case FINISHED = 'finished';
    case GETTING_ABANDONED = 'getting-abandoned';
    case ABANDONED = 'abandoned';
}
