<?php

namespace App\Http\Controllers\Webhook;

use App\Gateways\GatewayRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GatewayWebhookController extends Controller
{
    public function __construct(private GatewayRegistry $gateways) {}

    public function handle(Request $request, string $gateway): Response
    {
        try {
            $module = $this->gateways->get($gateway);
            $module->handleWebhook($request);
        } catch (\InvalidArgumentException) {
            abort(404);
        } catch (\Throwable $e) {
            report($e);
            return response('Webhook error', 500);
        }

        return response('OK', 200);
    }
}
