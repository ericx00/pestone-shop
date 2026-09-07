<?php

namespace App\Http\Controllers;

use App\Services\Payments\MpesaGateway;
use App\Services\Payments\PesapalGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function mpesa(Request $request, MpesaGateway $gateway)
    {
        Log::channel('stack')->info('M-Pesa callback', $request->all());

        try {
            $gateway->handleCallback($request);
        } catch (\Throwable $e) {
            Log::error('M-Pesa callback error: '.$e->getMessage());
        }

        // Safaricom expects this exact acknowledgement shape.
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function pesapal(Request $request, PesapalGateway $gateway)
    {
        Log::info('Pesapal IPN', $request->all());

        try {
            $gateway->handleCallback($request);
        } catch (\Throwable $e) {
            Log::error('Pesapal IPN error: '.$e->getMessage());
        }

        return response()->json([
            'orderNotificationType' => $request->input('OrderNotificationType', 'IPNCHANGE'),
            'orderTrackingId' => $request->input('OrderTrackingId'),
            'orderMerchantReference' => $request->input('OrderMerchantReference'),
            'status' => 200,
        ]);
    }
}
