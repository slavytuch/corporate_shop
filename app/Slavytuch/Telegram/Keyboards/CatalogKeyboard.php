<?php

namespace App\Slavytuch\Telegram\Keyboards;

use App\Models\User;
use App\Slavytuch\Shop\Global\Enums\UserPermissions;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionFunction;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionProcedure;
use App\Slavytuch\Telegram\Keyboards\Abstracts\BaseKeyboardAbstract;
use Illuminate\Pagination\LengthAwarePaginator;
use Telegram\Bot\Keyboard\Keyboard;

class CatalogKeyboard extends BaseKeyboardAbstract
{
    public function __construct(User $user, protected LengthAwarePaginator $itemList)
    {
        parent::__construct($user);
    }

    public function getKeyboard(): Keyboard
    {

        $buttonList = [];
        foreach ($this->itemList as $product) {
            $buttonList[] = [
                Keyboard::inlineButton(
                    ['text' => $product->name, 'callback_data' => ActionFunction::CATALOG_DISPLAY->value . $product->id]
                )
            ];
        }

        $navigationButtons = [];
        if ($this->itemList->currentPage() > 1) {
            $navigationButtons[] =
                Keyboard::inlineButton(
                    ['text' => '<- Предыдущая страница', 'callback_data' => ActionFunction::CATALOG_LIST->value . ($this->itemList->currentPage() - 1)]
                );
        }

        if ($this->itemList->hasMorePages()) {
            $navigationButtons[] =
                Keyboard::inlineButton(
                    ['text' => 'Следующая страница ->', 'callback_data' =>  ActionFunction::CATALOG_LIST->value . ($this->itemList->currentPage() + 1)]
                );
        }

        if (!empty($navigationButtons)) {
            $buttonList[] = $navigationButtons;
        }

        if ($this->user->can(UserPermissions::ACCESS_CATALOG->value)) {
            $buttonList[] = [
                Keyboard::inlineButton(
                    ['text' => 'Добавить товар', 'callback_data' => ActionProcedure::ADD_PRODUCT->value]
                )
            ];
        }


        return Keyboard::make([
            'inline_keyboard' => $buttonList
        ]);
    }
}
