<?php

namespace App\Slavytuch\Telegram\Inline\Factories;

use App\Models\User;
use App\Slavytuch\Telegram\Inline\Abstracts\InlineActionInterface;
use App\Slavytuch\Telegram\Inline\Actions\Catalog\Buy;
use App\Slavytuch\Telegram\Inline\Actions\Catalog\DisplayProduct;
use App\Slavytuch\Telegram\Inline\Actions\Catalog\ListPage;
use App\Slavytuch\Telegram\Inline\Actions\DisplayText;
use App\Slavytuch\Telegram\Inline\Actions\Exceptions\ActionNotFoundException;
use App\Slavytuch\Telegram\Inline\Actions\Personal\CancelOrder;
use App\Slavytuch\Telegram\Inline\Actions\Personal\ChangeName;
use App\Slavytuch\Telegram\Inline\Actions\Personal\DisplayOrder;
use App\Slavytuch\Telegram\Inline\Actions\Personal\GiveCurrency;
use App\Slavytuch\Telegram\Inline\Actions\Personal\OrderList;
use App\Slavytuch\Telegram\Util\UserHelper;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\CallbackQuery;

class InlineActionFactory
{
    protected User $user;

    public function __construct(protected Api $telegram, protected CallbackQuery $relatedObject)
    {
        $this->user = UserHelper::getUserByTelegramId($this->relatedObject->from->id);
    }

    public function getAction(): InlineActionInterface
    {
        try {
            $className = $this->getActionClassName();
        } catch (ActionNotFoundException) {
            $this->telegram->answerCallbackQuery(['text' => 'Кнопка не работает - не знаю что делать']);
        }

        return new $className ($this->telegram, $this->relatedObject, $this->user);
    }

    protected function getActionClassName(): string
    {
        $data = $this->relatedObject->data;

        if ($data === 'personal:change-name') {
            return ChangeName::class;
        }

        if ($data === 'personal:order-list') {
            return OrderList::class;
        }

        if (str_starts_with($data, 'catalog:display:')) {
            return DisplayProduct::class;
        }

        if (str_starts_with($data, 'catalog:list:')) {
            return ListPage::class;
        }

        if (str_starts_with($data, 'catalog:buy:')) {
            return Buy::class;
        }

        if (str_starts_with($data, 'display-text:')) {
            return DisplayText::class;
        }

        if (str_starts_with($data, 'personal:display-order:')) {
            return DisplayOrder::class;
        }
        if (str_starts_with($data, 'personal:cancel-order:')) {
            return CancelOrder::class;
        }
        if ($data === 'personal:give-currency') {
            return GiveCurrency::class;
        }

        throw new ActionNotFoundException('Действие не определено');
    }
}
