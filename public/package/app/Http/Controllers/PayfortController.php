<?php


namespace App\Http\Controllers;

use App\Payment;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MoeenBasra\Payfort\PayfortFacade as Payfort;

class PayfortController
{
    /** @var  \MoeenBasra\Payfort\MerchantPage\MerchantPage */
    protected $provider;

    public function __construct()
    {
        $this->provider = Payfort::configure(config('payfort'));
    }

    public function create()
    {
        $response = $this->provider->prepareTokenizationData([
            'token_name' => Uuid::uuid4()->getHex(),
            'merchant_reference' => Uuid::uuid4()->getHex(),
            'return_url' => config('app.url') . '/payfort/tokenization',
        ]);

        return response()->json([
            'type' => 'form',
            'url' => $this->provider->getClient()->getTokenizationUrl(),
            'data' => $response,
        ]);
    }

    public function handleTokenResponse(Request $request)
    {
        $input = $request->all();

        Log::info('tokenization response received from payfort:' . PHP_EOL . print_r($input, 1));

        $this->provider->verifyResponse($input);

        // create new payment object
        $payment = new Payment();

        /** set customer token from the input */
        $payment->setCustomerToken(Arr::get($input, 'token_name'));

        // prepare payment data
        $data = $this->provider->authorization([
            'command' => 'PURCHASE',
            'merchant_reference' => Uuid::uuid4()->getHex(),
            'token_name' => $payment->getCustomerToken(),
            'amount' => $this->provider->convertAmountToPayfortFormat($payment->getAmount()),
            'customer_email' => $payment->getCustomerEmail(),
            'customer_ip' => $payment->getCustomerIp(),
            'return_url' => config('app.url') . '/payfort/response',
        ]);

        if ($data['3ds_url']) {
            return redirect()->away($data['3ds_url']);
        }

        return response()->json($data);
    }

    public function handleResponse(Request $request)
    {
        $input = $request->all();

        Log::info('purchase response received from payfort:' . PHP_EOL . print_r($input, 1));

        $this->provider->verifyResponse($input);

        return response()->json($input);
    }

    public function handleError(Request $request)
    {
        $input = $request->all();

        Log::info('error received from payfort:' . PHP_EOL . print_r($input, 1));

        $this->provider->verifyResponse($input);

        return response()->json($input);
    }

    public function handleCallback(Request $request)
    {
        $input = $request->all();

        Log::info('callback received from payfort:' . PHP_EOL . print_r($input, 1));

        $this->provider->verifyResponse($input);

        return response()->json($input);
    }
}
