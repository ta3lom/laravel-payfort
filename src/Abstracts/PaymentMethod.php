<?php


namespace MoeenBasra\Payfort\Abstracts;

use Money\Money;
use Money\Currency;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use GuzzleHttp\Cookie\CookieJar;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
use MoeenBasra\Payfort\ValidationRules;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Validator;
use Money\Formatter\DecimalMoneyFormatter;
use MoeenBasra\Payfort\Services\HttpClient;
use Illuminate\Validation\ValidationException;
use MoeenBasra\Payfort\Exceptions\PayfortException;
use MoeenBasra\Payfort\Exceptions\IncompletePayment;

abstract class PaymentMethod
{
    /**
     * Access code
     *
     * @var string required|alphanumeric|max:20
     *
     * Example: zx0IPmPy5jp1vAz8Kpg7
     */
    public $access_code;
    /**
     * The ID of the Merchant.
     *
     * @var string required|alphanumeric|max:20
     *
     * Example: CycHZxVj
     */
    public $merchant_identifier;
    /**
     * The checkout page and messages language.
     *
     * @var string required|alpha|min:2|max:2
     *
     * Possible/ expected values: en/ar
     */
    public $language;
    /**
     * The currency of the transaction’s amount in ISO code 3.
     *
     * @var string required|alpha|max:3
     *
     * Example: AED
     */
    public $currency;
    /**
     * the string phrase use to encode the request
     *
     * @var string required|string
     *
     * Example: TESTSHAIN
     */
    public $sha_request_phrase;
    /**
     * the string phrase use to encode the response
     *
     * @var string required|string
     *
     * Example: TESTSHAOUT
     */
    public $sha_response_phrase;
    /**
     * the encryption use to make the signature
     *
     * @var string
     */
    public $encryption = 'sha256';
    /**
     * the payment method used for the payments
     *
     * @var string
     */
    public $payment_method;
    /**
     * switch the sandbox mode
     *
     * @var bool
     */
    public $is_sandbox = true;
    /**
     * the gateway host path
     *
     * @var string
     *
     * @deprecated
     */
    public $gateway_url;

    /**
     * the base configuration
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * get the validation rules
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * set validation rules
     *
     * @param array $rules
     */
    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }

    /**
     * get the configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * configure the Payfort payment_method
     *
     * @param array $config
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function configure(array $config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
            $this->validateConfig($this->config);
        }

        $this->access_code = Arr::get($this->config, 'access_code');
        $this->merchant_identifier = Arr::get($this->config, 'merchant_identifier');
        $this->sha_request_phrase = Arr::get($this->config, 'sha_request_phrase');
        $this->sha_response_phrase = Arr::get($this->config, 'sha_response_phrase');

        $this->language = Arr::get($this->config, 'language');
        $this->currency = strtoupper(Arr::get($this->config, 'currency'));
        $this->is_sandbox = Arr::get($this->config, 'is_sandbox', true);
        $this->gateway_url = $this->getGatewayUrl();

        if (null === $this->client) {
            $this->client = $this->getClient();
        }
    }

    /**
     * convert amount to payfort format
     *
     * @param string $amount
     *
     * @return string
     */
    public function convertAmountToPayfortFormat(string $amount): string
    {
        $currencies = new ISOCurrencies();

        $moneyParser = new DecimalMoneyParser($currencies);

        $money = $moneyParser->parse($amount, $this->currency);

        return $money->getAmount();
    }

    /**
     * revert amount to original format
     *
     * @param string $amount
     *
     * @return string
     */
    public function revertAmountFromPayfortFormat(string $amount): string
    {
        $money = new Money($amount, new Currency($this->currency));
        $currencies = new ISOCurrencies();

        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return $moneyFormatter->format($money);
    }

    /**
     * verify the response
     *
     * @param array $params
     *
     * @throws PayfortException
     */
    public function verifyResponse(array $params)
    {
        // if parameters are empty throw exception
        if (empty($params)) {
            throw new PayfortException('The response data can not be empty');
        }

        $response_code = Arr::get($params, 'response_code');

        // if response is not successful
        // or response doesn't have 3ds url
        // throw exception
        if (substr($response_code, 2) !== '000' && substr($response_code, 2) !== '064') {
            throw new PayfortException(Arr::get($params, 'response_message', 'Invalid payment status'));
        }

        $response_signature = Arr::get($params, 'signature');
        unset($params['signature']);

        $signature = $this->createSignature($params, 'response');

        // if signature mismatch throw exception
        if ($signature !== $response_signature) {
            throw new PayfortException('Signature mismatch');
        }
    }

    /**
     * create the data signature
     *
     * @param array $input
     * @param string $type
     *
     * @return string
     */
    public function createSignature(array $input, string $type = 'request'): string
    {
        $string = '';
        ksort($input);
        foreach ($input as $k => $v) {
            $string .= "$k=$v";
        }

        if ($type == 'request') {
            $string = $this->sha_request_phrase . $string . $this->sha_request_phrase;
        } else {
            $string = $this->sha_response_phrase . $string . $this->sha_response_phrase;
        }

        return hash($this->encryption, $string);
    }

    /**
     * call the api
     *
     * @param array $data
     * @param bool $is_json
     * @param string $url
     *
     * @return mixed|string
     * @throws \Exception
     *
     * @deprecated
     */
    public function callApi(array $data, bool $is_json = true, string $url = null)
    {
        $content_type = $is_json ? 'json' : 'form_params';
        $payload = [
            $content_type => $data,
        ];

        $client = new Client([
            'curl' => [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => false,
                CURLOPT_FAILONERROR => true,
            ],
            'cookies' => new CookieJar(),
            'allow_redirects' => true,
            'debug' => false,
        ]);

        try {
            $url = $url ?? $this->gateway_url . 'FortAPI/paymentApi';

            $response = $client->post($url, $payload);

        } catch (ClientException $exception) {
            throw new IncompletePayment($exception->getMessage(), $exception->getCode());
        }

        $content = $response->getBody()->getContents();

        if ($is_json) {
            $content = json_decode($content, true);
        }

        $this->verifyResponse($content);

        return $content;
    }

    /**
     * get the gateway url
     *
     * @return string
     *
     * @deprecated
     */
    public function getGatewayUrl(): string
    {
        if ($this->is_sandbox) {
            return 'https://sbcheckout.payfort.com/';
        }
        return 'https://checkout.payfort.com/';
    }

    /**
     * @return \MoeenBasra\Payfort\Services\HttpClient
     */
    public function getClient(): HttpClient
    {
        return app(HttpClient::class, ['is_sandbox' => $this->is_sandbox]);
    }

    /**
     * validate configuration
     *
     * @param array $config
     *
     * @throws ValidationException
     */
    protected function validateConfig(array $config)
    {
        $validator = Validator::make($config, ValidationRules::configRules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
