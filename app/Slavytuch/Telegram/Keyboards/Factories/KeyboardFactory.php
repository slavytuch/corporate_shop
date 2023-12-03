<?php

namespace App\Slavytuch\Telegram\Keyboards\Factories;

use App\Models\User;
use App\Slavytuch\Telegram\Keyboards\Abstracts\KeyboardInterface;
use App\Slavytuch\Telegram\Keyboards\CatalogKeyboard;
use App\Slavytuch\Telegram\Keyboards\Enums\KeyboardType;
use App\Slavytuch\Telegram\Keyboards\PersonalKeyboard;
use App\Slavytuch\Telegram\Keyboards\ProductKeyboard;

class KeyboardFactory
{
    protected array $registry = [
        KeyboardType::CATALOG->value => CatalogKeyboard::class,
        KeyboardType::PERSONAL->value => PersonalKeyboard::class,
        KeyboardType::PRODUCT->value => ProductKeyboard::class,
    ];

    public function __construct(protected readonly User $user)
    {

    }

    public function getKeyboard(KeyboardType $keyboard): ?KeyboardInterface
    {
        $className = $this->registry[$keyboard->value];

        return new $className($this->user);
    }
}
