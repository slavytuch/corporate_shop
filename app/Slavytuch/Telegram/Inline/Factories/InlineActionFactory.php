<?php

namespace App\Slavytuch\Telegram\Inline\Factories;

use App\Models\User;
use App\Slavytuch\Telegram\Inline\Abstracts\InlineActionInterface;
use App\Slavytuch\Telegram\Inline\Actions\Catalog\Buy;
use App\Slavytuch\Telegram\Inline\Actions\Catalog\DisplayProduct;
use App\Slavytuch\Telegram\Inline\Actions\Catalog\ListPage;
use App\Slavytuch\Telegram\Inline\Actions\DisplayText;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionFunction;
use App\Slavytuch\Telegram\Inline\Actions\Enums\ActionProcedure;
use App\Slavytuch\Telegram\Inline\Actions\Exceptions\ActionNotFoundException;
use App\Slavytuch\Telegram\Inline\Actions\Personal\Admin\SetManager;
use App\Slavytuch\Telegram\Inline\Actions\Personal\CancelOrder;
use App\Slavytuch\Telegram\Inline\Actions\Personal\ChangeName;
use App\Slavytuch\Telegram\Inline\Actions\Personal\DisplayOrder;
use App\Slavytuch\Telegram\Inline\Actions\Personal\GiveCurrency;
use App\Slavytuch\Telegram\Inline\Actions\Personal\Manager\ChangeOrderStatusToCanceled;
use App\Slavytuch\Telegram\Inline\Actions\Personal\Manager\ChangeOrderStatusToFinished;
use App\Slavytuch\Telegram\Inline\Actions\Personal\Manager\ChangeOrderStatusToProcessing;
use App\Slavytuch\Telegram\Inline\Actions\Personal\Manager\ChangeOrderStatusToReady;
use App\Slavytuch\Telegram\Inline\Actions\Personal\OrderList;
use App\Slavytuch\Telegram\Util\UserHelper;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\CallbackQuery;
use \App\Slavytuch\Telegram\Inline\Actions\Personal\Manager\OrderList as ManagerOrderList;

class InlineActionFactory
{
    protected User $user;

    protected array $actionProcedureRegistry = [
        ActionProcedure::CHANGE_NAME->value => ChangeName::class,
        ActionProcedure::GIVE_CURRENCY->value => GiveCurrency::class,
        ActionProcedure::ORDER_LIST->value => OrderList::class,
        ActionProcedure::ALL_ORDERS->value => ManagerOrderList::class,
        ActionProcedure::SET_MANAGER->value => SetManager::class,
    ];

    protected array $actionFunctionRegistry = [
        ActionFunction::CANCEL_ORDER->value => CancelOrder::class,
        ActionFunction::CATALOG_BUY->value => Buy::class,
        ActionFunction::CATALOG_DISPLAY->value => DisplayProduct::class,
        ActionFunction::DISPLAY_ORDER->value => DisplayOrder::class,
        ActionFunction::DISPLAY_TEXT->value => DisplayText::class,
        ActionFunction::CATALOG_LIST->value => ListPage::class,
        ActionFunction::MANAGER_CANCEL_ORDER->value => ChangeOrderStatusToCanceled::class,
        ActionFunction::ORDER_FINISHED->value => ChangeOrderStatusToFinished::class,
        ActionFunction::ORDER_PROCESSING->value => ChangeOrderStatusToProcessing::class,
        ActionFunction::ORDER_READY->value => ChangeOrderStatusToReady::class,
    ];

    public function __construct(protected Api $telegram, protected CallbackQuery $relatedObject)
    {
        $this->user = UserHelper::getUserByTelegramId($this->relatedObject->from->id);
    }

    public function getAction(): ?InlineActionInterface
    {
        try {
            $className = $this->getActionClassName();
            return new $className ($this->telegram, $this->relatedObject, $this->user);
        } catch (ActionNotFoundException) {
            $this->telegram->answerCallbackQuery(['text' => 'Кнопка не работает - не знаю что делать']);
            return null;
        }
    }

    protected function getActionClassName(): string
    {
        $data = $this->relatedObject->data;

        foreach ($this->actionProcedureRegistry as $code => $handler) {
            if ($code === $data) {
                return $handler;
            }
        }

        foreach ($this->actionFunctionRegistry as $code => $handler) {
            if (str_starts_with($data, $code)) {
                return $handler;
            }
        }

        throw new ActionNotFoundException('Действие не определено');
    }
}
