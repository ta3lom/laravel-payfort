<?php


namespace MoeenBasra\Payfort;

class ValidationRules
{
    /**
     * available payment options
     *
     * @var array
     */
    public static $payment_options = [
        'MASTERCARD',
        'VISA',
        'AMEX',
        'MADA', //  (for Purchase operations and eci Ecommerce only)
        'MEEZA', // (for Purchase operations and ECOMMERCE eci only)
    ];

    /**
     * available ecommerce indicators
     *
     * @var array
     */
    public static $avaiable_eci = [
        'ECOMMERCE',
        'MOTO',
        'RECCURING',
    ];

    /**
     * available commands
     *
     * @var array
     */
    public static $commands = [
        'authorization' => 'AUTHORIZATION',
        'purchase' => 'PURCHASE',
    ];

    public static $service_commands = [
        'TOKENIZATION',
        'SDK_TOKEN',
        'GET_BATCH_RESULTS',
        'VERIFY_CARD',
        'PROCESS_BATCH',
        'UPLOAD_BATCH_FILE',
        'CURRENCY_CONVERSION',
        'CREATE_TOKEN',
        'BILL_PRESENTMENT',
        'PAYMENT_LINK',
    ];

    public static $query_commands = [
        'CHECK_VERIFY_CARD_STATUS',
        'GENERATE_REPORT',
        'CHECK_STATUS',
        'GET_TOKENS',
        'DOWNLOAD_REPORT',
        'GET_REPORT',
        'GET_INSTALLMENTS_PLANS',

    ];

    /**
     * basic configuration rules
     *
     * @return array
     */
    public static function configRules()
    {
        return [
            'access_code' => 'required|alpha_num|max:20',
            'merchant_identifier' => 'required|alpha_num|max:20',
            'encryption' => 'required|alpha_num|max:10',
            'sha_request_phrase' => 'required|string',
            'sha_response_phrase' => 'required|string',
            'language' => 'required|max:2',
            'currency' => 'required|max:3',
            'payment_method' => 'required|in:merchant_page_2',
            'is_sandbox' => 'boolean',
        ];
    }
}
