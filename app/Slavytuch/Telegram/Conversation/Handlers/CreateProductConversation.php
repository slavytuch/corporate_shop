<?php

namespace App\Slavytuch\Telegram\Conversation\Handlers;

use App\Models\PriceType;
use App\Models\Product;
use App\Slavytuch\Telegram\Conversation\Abstracts\BaseConversationAbstract;
use App\Slavytuch\Telegram\Conversation\Abstracts\HasAbandonDialogueInterface;
use App\Slavytuch\Telegram\Conversation\Traits\HasAbandonDialogue;
use App\Slavytuch\Telegram\Keyboards\ProductKeyboard;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class CreateProductConversation extends BaseConversationAbstract
{
    /**
     * Спрашиваем у пользователя как к нему обращаться
     */
    public function init(): ?string
    {
        $this->reply(
            [
                'text' => 'Добвляем товар, первый шаг - название. Для отмены - напиши "Отмена" для сброса',
            ]
        );

        return 'name';
    }

    public function name()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage()->text;

        if (mb_strtolower($message) === 'отмена') {
            $this->reply([
                'text' => 'Отменил',
                'reply_markup' => Keyboard::remove(),
            ]);
            return null;
        }

        $this->reply([
            'text' => 'Принято, теперь - описание. Если его нет - напиши "Нет"'
        ]);

        return 'description';
    }

    public function description()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage()->text;

        if (mb_strtolower($message) === 'отмена') {
            $this->reply([
                'text' => 'Отменил',
                'reply_markup' => Keyboard::remove(),
            ]);
            return null;
        }

        $this->reply(['text' => 'Принято, следующий шаг - картинка. Если она не нужна - напиши "Нет"']);

        return 'picture';
    }

    public function picture()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage()->text;

        if (mb_strtolower($message) === 'отмена') {
            $this->reply([
                'text' => 'Отменил',
                'reply_markup' => Keyboard::remove(),
            ]);
            return null;
        }

        $this->reply([
            'text' => 'Отлично, осталось самое сложное - цены. Введи цену в формате: ' .
                PHP_EOL . '<Название цены 1> - <Цена 1>' . PHP_EOL . '<Название цены 2> - <Цена 2>'
        ]);

        return 'prices';
    }

    public function prices()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage()->text;

        if (mb_strtolower($message) === 'отмена') {
            $this->reply([
                'text' => 'Отменил',
                'reply_markup' => Keyboard::remove(),
            ]);
            return null;
        }

        $rows = explode(PHP_EOL, $message);

        $prices = [];
        foreach ($rows as $row) {
            $parts = explode(' - ', $row);
            $prices[$parts[0]] = $parts[1];
        }

        $this->reply([
            'text' => 'Хорошо, товар сформирован, всё верно?',
            'reply_markup' => Keyboard::make([
                'keyboard' => [['Да', 'Нет']],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ])
        ]);

        $name = $this->history->where('last_stage', 'name')->first()->response;
        $description = $this->history->where('last_stage', 'description')->first()->response;
        $picture = $this->history->where('last_stage', 'picture')->first()->response;
        $caption = $name . PHP_EOL . PHP_EOL;

        foreach ($prices as $name => $price) {
            $caption .= $name . ': ' . $price . PHP_EOL;
        }

        if ($description && $description !== 'Нет') {
            $caption .= PHP_EOL . $description;
        }

        if ($picture && $picture !== 'Нет') {
            $this->telegram->sendPhoto([
                'chat_id' => $this->telegram->getWebhookUpdate()->getChat()->get('id'),
                'photo' => $picture,
                'caption' => $caption,
            ]);
        } else {
            $this->reply([
                'text' => $caption,
            ]);
        }


        return 'create';
    }

    /**
     * Создаём
     */
    public function create()
    {
        $confirm = $this->telegram->getWebhookUpdate()->getMessage()->text;
        switch ($confirm) {
            case 'Да':
                $name = $this->history->where('last_stage', 'name')->first()->response;
                $description = $this->history->where('last_stage', 'description')->first()->response;
                $picture = $this->history->where('last_stage', 'picture')->first()->response;
                $prices = $this->history->where('last_stage', 'prices')->first()->response;

                $newProduct = new Product();

                $newProduct->name = $name;
                if ($description && $description !== 'Нет') {
                    $newProduct->description = $description;
                }

                if ($picture && $picture !== 'Нет') {
                    $newProduct->picture = $picture;
                }

                $newProduct->save();

                $rows = explode(PHP_EOL, $prices);
                foreach ($rows as $row) {
                    [$priceName, $price] = explode(' - ', $row);

                    $newProduct->prices()->attach(PriceType::where('name', $priceName)->first()->id, [
                        'price' => $price
                    ]);
                }

                $this->reply(['text' => $name . ' создан!', 'reply_markup' => Keyboard::remove()]);
                break;
            case 'Нет':
                $this->reply(['text' => 'Хорошо, в следующий раз', 'reply_markup' => Keyboard::remove()]
                );
                break;
            default:
        }

        return null;
    }
}
