<?php

namespace App\Http\Controllers;

use App\Slavytuch\Telegram\Webhook\WebhookManager;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(WebhookManager $webhookManager, Request $request)
    {
        try {
            $webhookManager->handleRequest($request);
        } catch (\Exception $ex) {
            \Log::channel('webhook')->error('Исключение при обработке в контроллере', ['exception' => $ex]);
            return 'error!';
        }

        return 'ok';
    }
}
