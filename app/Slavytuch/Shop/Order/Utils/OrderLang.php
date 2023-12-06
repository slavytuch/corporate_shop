<?php

namespace App\Slavytuch\Shop\Order\Utils;

use App\Slavytuch\Shop\Order\Enums\Status;

class OrderLang
{
    protected static array $unknownStatuses = [
        'Непонятно',
        'Не знаю',
        'Спроси попозже',
        'Не в курсе',
        'Кто-то забыл добавить статус сюда, сори :)',
        'Сыр с маслом',
        'Бардак'
    ];

    /**
     * Выдаёт человекопонятный статус заказа
     *
     * @param Status $status
     * @return string
     */
    public static function getStatusName(Status $status): string
    {
        return match ($status) {
            Status::CREATED => 'Создан',
            Status::PROCESSING => 'В работе',
            Status::READY => 'Готов к выдаче',
            Status::FINISHED => 'Выдан',
            Status::CANCELLED => 'Отменён',
            default => self::$unknownStatuses[array_rand(self::$unknownStatuses)]
        };
    }
}
