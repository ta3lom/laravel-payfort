<?php


namespace MoeenBasra\Payfort\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use MoeenBasra\Payfort\Exceptions\IncompletePayment;

class HttpClient
{
    /**
     * @var array
     */
    public static $config = [];
    /**
     * @var bool
     */
    protected $is_sandbox;

    public function __construct(bool $is_sandbox = true)
    {
        $this->is_sandbox = $is_sandbox;
    }

    /**
     * create the token
     *
     * @param array $data
     *
     * @return array
     * @throws \MoeenBasra\Payfort\Exceptions\IncompletePayment
     */
    public function createToken(array $data): array
    {
        $url = $this->getTokenizationUrl();

        $payload = [
            'form_params' => $data,
        ];

        return $this->send($url, $payload);
    }

    /**
     * authorize the payment
     *
     * @param array $data
     *
     * @return array
     * @throws \MoeenBasra\Payfort\Exceptions\IncompletePayment
     */
    public function authorizeTransaction(array $data): array
    {
        $url = $this->getAuthorizationUrl();

        $payload = [
            'json' => $data,
        ];

        return $this->send($url, $payload);
    }

    /**
     * check the status of the transaction
     *
     * @param array $data
     *
     * @return array
     * @throws \MoeenBasra\Payfort\Exceptions\IncompletePayment
     */
    public function checkStatus(array $data): array
    {
        $url = $this->getVerificationUrl();

        $payload = [
            'json' => $data,
        ];

        return $this->send($url, $payload);
    }

    /**
     * call the api
     *
     * @param string $url
     * @param array $payload
     *
     * @return array
     * @throws \MoeenBasra\Payfort\Exceptions\IncompletePayment
     */
    public function send(string $url, array $payload): array
    {
        try {
            $response = $this->getClient()->post($url, $payload);

        } catch (ClientException $exception) {
            throw new IncompletePayment($exception->getMessage(), $exception->getCode());
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * get default config
     *
     * @return array
     */
    public function defaultConfig(): array
    {
        return [
            'curl' => [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => false,
                CURLOPT_FAILONERROR => true,
            ],
            'cookies' => new CookieJar(),
            'allow_redirects' => true,
            'debug' => $this->is_sandbox ? true : false,
        ];
    }

    /**
     * get transaction verification url
     *
     * @return string
     */
    public function getVerificationUrl(): string
    {
        return $this->is_sandbox
            ? 'https://sbpaymentservices.PayFort.com/FortAPI/paymentApi'
            : 'https://paymentservices.PayFort.com/FortAPI/paymentApi';
    }

    /**
     * get transaction authorization url
     *
     * @return string
     */
    public function getAuthorizationUrl(): string
    {
        return $this->is_sandbox
            ? 'https://sbpaymentservices.payfort.com/FortAPI/paymentApi'
            : 'https://paymentservices.payfort.com/FortAPI/paymentApi';
    }

    /**
     * get tokenization url
     *
     * @return string
     */
    public function getTokenizationUrl(): string
    {
        return $this->is_sandbox
            ? 'https://sbcheckout.payfort.com/FortAPI/paymentApi'
            : 'https://checkout.PayFort.com/FortAPI/paymentPage';
    }

    /**
     * configure and get client
     *
     * @return \GuzzleHttp\Client
     */
    protected function getClient(): Client
    {
        $config = $this->defaultConfig();

        if (!empty(static::$config)) {
            $config = array_merge($config, self::$config);
        }

        return new Client($config);
    }
}
