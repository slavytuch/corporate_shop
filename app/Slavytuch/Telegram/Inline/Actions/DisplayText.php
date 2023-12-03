<?php

namespace App\Slavytuch\Telegram\Inline\Actions;

use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;

class DisplayText extends BaseInlineActionAbstract
{
    public function process()
    {
        $parts = explode(':', $this->relatedObject->data);
        $this->answer($parts[array_key_last($parts)]);
    }
}
