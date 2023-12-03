<?php

namespace App\Slavytuch\Telegram\Inline\Actions\Catalog;

use App\Slavytuch\Telegram\Inline\Abstracts\BaseInlineActionAbstract;

class Search extends BaseInlineActionAbstract
{

    public function process()
    {


        $this->answer();
    }
}
