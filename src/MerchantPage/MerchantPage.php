<?php


namespace MoeenBasra\Payfort\MerchantPage;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MoeenBasra\Payfort\Abstracts\PaymentMethod;

class MerchantPage extends PaymentMethod
{
    public function __construct(array $config)
    {
        $this->configure($config);
    }

    /**
     * authorize the tokenized transaction
     *
     * @param array $params
     *
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authorization(array $params): array
    {
        $params = array_merge([
            'command' => 'AUTHORIZATION',
            'access_code' => $this->access_code,
            'merchant_identifier' => $this->merchant_identifier,
            'language' => $this->language,
            'currency' => $this->currency,
        ], $params);

        // if signature is not already set
        if (!$signature = Arr::get($params, 'signature')) {
            // create signature
            $signature = $this->createSignature($params);

            // add signature in params
            $params['signature'] = $signature;
        }

        // get the validated data for authorization
        $validator = Validator::make($params, ValidationRules::authorization());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // call api server to validate the data
        return $this->callApi($params);
    }

    /**
     * get the data for the merchant page
     *
     * @param array $params
     *
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function prepareTokenizationData(array $params): array
    {
        $params = array_merge([
            'service_command' => 'TOKENIZATION',
            'access_code' => $this->access_code,
            'merchant_identifier' => $this->merchant_identifier,
            'language' => $this->language,
            'currency' => $this->currency,
        ], $params);

        // if signature is not already set
        if (!$signature = Arr::get($params, 'signature')) {

            // create the signature
            $signature = $this->createSignature(
                Arr::except($params, [
                    'card_security_code',
                    'card number',
                    'expiry_date',
                    'card_holder_name',
                    'remember_me',
                ])
            );

            // add signature in the params
            $params['signature'] = $signature;
        }

        // validate and return the valid data for merchant page
        $validator = Validator::make($params, ValidationRules::tokenization());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $params;
    }

    /**
     * check the transaction status
     *
     * @param array $params
     *
     * @return array
     * @throws \Exception
     */
    public function checkTransactionStatus(array $params): array
    {
        $params = array_merge([
            'query_command' => 'CHECK_STATUS',
            'access_code' => $this->access_code,
            'merchant_identifier' => $this->merchant_identifier,
            'language' => $this->language,
        ], $params);

        // if signature is not already set
        if (!$signature = Arr::get($params, 'signature')) {

            // create the signature
            $signature = $this->createSignature($params);

            // add signature in the params
            $params['signature'] = $signature;
        }

        return $this->callApi($params, true, 'https://sbpaymentservices.payfort.com/FortAPI/paymentApi');
    }
}
