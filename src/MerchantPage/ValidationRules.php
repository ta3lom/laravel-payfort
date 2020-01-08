<?php


namespace MoeenBasra\Payfort\MerchantPage;

use MoeenBasra\Payfort\ValidationRules as BaseRules;

class ValidationRules extends BaseRules
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function tokenization()
    {
        return [
            'service_command' => 'required|in:' . implode(',', self::$available_commands),
            'access_code' => 'required|alpha_num|max:20',
            'merchant_identifier' => 'required|alpha_num|max:20',
            'merchant_reference' => 'required|string|max:40',
            'currency' => 'required|alpha|max:3',
            'language' => 'required|alpha|max:2',
            'signature' => 'required|max:200',
            'expiry_date' => 'numeric|max:4',
            'card_number' => 'numeric|max:19',
            'card_security_code' => 'numeric|max:3',
            'card_holder_name' => 'string|max:50',
            'token_name' => 'max:100',
            'remember_me' => 'in:YES,NO|max:3',
            'return_url' => 'max:400',
        ];
    }

    public static function authorization()
    {
        return [
            'command' => 'required|in:' . implode(',', self::$available_commands),
            'access_code' => 'required|alpha_num|max:20',
            'merchant_identifier' => 'required|alpha_num|max:20',
            'merchant_reference' => 'required|string|max:40',
            'amount' => 'required|numeric',
            'currency' => 'required|alpha|max:3',
            'language' => 'required|alpha|max:2',
            'customer_email' => 'required|email',
            'customer_ip' => 'required|ip',
            'token_name' => 'required|max:100',
            'signature' => 'required|max:200',
            'payment_option' => 'in:' . implode(',', self::$available_payment_options),
            'eci' => 'in:' . implode(',', self::$avaiable_eci),
            'order_description' => 'max:150',
            'card_security_code' => 'numeric|max:4',
            'customer_name' => 'string|max:40',
            'merchant_extra' => 'string|max:999',
            'merchant_extra1' => 'string|max:250',
            'merchant_extra2' => 'string|max:250',
            'merchant_extra3' => 'string|max:250',
            'merchant_extra4' => 'string|max:250',
            'merchant_extra5' => 'string|max:250',
            'remember_me' => 'in:YES,NO|max:3',
            'phone_number' => 'max:19',
            'settlement_reference' => 'string|max:34',
            'return_url' => 'max:400',
        ];
    }

    public static function checkStatus()
    {
        return [
            'query_command' => 'required|in:CHECK_STATUS',
            'access_code' => 'required|alpha_num|max:20',
            'merchant_identifier' => 'required|alpha_num|max:20',
            'merchant_reference' => 'required|string|max:40',
            'language' => 'required|alpha|max:2',
            'signature' => 'required|max:200',
            'fort_id' => 'numeric|max:20',
            'return_third_party_response_codes' => 'in:YES,NO',
        ];
    }
}
