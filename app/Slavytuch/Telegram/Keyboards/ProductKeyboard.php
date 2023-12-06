<?php

namespace App\Slavytuch\Telegram\Keyboards;

use App\Models\Product;
use App\Models\User;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionFunction;
use App\Slavytuch\Telegram\Keyboards\Abstracts\BaseKeyboardAbstract;
use App\Slavytuch\Telegram\Keyboards\Abstracts\KeyboardInterface;
use Telegram\Bot\Keyboard\Keyboard;

class ProductKeyboard extends BaseKeyboardAbstract
{
    public function __construct(User $user, protected Product $product)
    {
        parent::__construct($user);
    }

    public function getKeyboard(): Keyboard
    {
        $priceList = $this->product->prices()->get();

        $buyButtonList = [];
        foreach ($priceList as $price) {
            $buyButtonList[] = Keyboard::inlineButton(
                [
                    'text' => 'Купить за ' . $price->name,
                    'callback_data' => ActionFunction::CATALOG_BUY->value . $this->product->id .
                        ':priceType:' . $price->id
                ]
            );
        }

        return Keyboard::make([
            'inline_keyboard' => [$buyButtonList]
        ]);
    }
}
