<?php


namespace Tests\Unit;

use Tests\TestCase;
use MoeenBasra\Payfort\MerchantPage\MerchantPage;

/**
 * Class MerchantPageTest
 *
 * @package Tests\Unit
 *
 * @property MerchantPage $intent
 */
class MerchantPageTest extends TestCase
{
    /** @var MerchantPage */
    protected $intent;

    /** @test */
    public function test_convert_amount_to_fort_format()
    {
        $amount = $this->intent->convertAmountToPayfortFormat('100.002');

        $this->assertEquals(100002, $amount);

    }

    /** @test */
    public function test_revert_amount_fort_format()
    {
        $amount = $this->intent->revertAmountFromPayfortFormat(100002);

        $this->assertEquals(100.002, $amount);
    }

    /** @test */
    public function test_create_signature()
    {
        $input = $this->config;
        $string = '';

        // sort array with keys asc
        ksort($input);

        foreach ($input as $k => $v) {
            $string .= "$k=$v";
        }

        // pre and post fix the string
        $string = $this->intent->sha_request_phrase . $string . $this->intent->sha_request_phrase;

        // create a hash for string
        $hash = hash($this->config['encryption'], $string);

        // create signature
        $signature = $this->intent->createSignature($this->config);

        $this->assertEquals($hash, $signature);
    }

    public function test_response()
    {
        $data = [
            'amount' => '20000',
            'response_code' => '02000',
            'card_number' => '531358******3430',
            'card_holder_name' => 'Moeen',
            'signature' => 'cf5e2bea49a4a7a8e59fd6f22039854e031e7c61f26ad7e6b01ed17cde5bb348',
            'merchant_identifier' => $this->intent->merchant_identifier,
            'access_code' => $this->intent->access_code,
            'payment_option' => 'MASTERCARD',
            'expiry_date' => '2105',
            'customer_ip' => '::1',
            'language' => 'en',
            'eci' => 'ECOMMERCE',
            'fort_id' => '157021813100093129',
            'command' => 'AUTHORIZATION',
            'response_message' => 'Success',
            'merchant_reference' => '2089355639',
            'authorization_code' => '959903',
            'customer_email' => 'test@payfort.com',
            'token_name' => 'ajar-pay-1570218127',
            'currency' => 'KWD',
            'customer_name' => 'Moeen Basra',
            'status' => '02',
        ];

        $response = $this->intent->verifyResponse($data);

        $this->assertNull($response);
    }

    /** @test */
    public function test_prepare_tokenized_data()
    {
        $input = $this->intent->prepareTokenizationData([
            'service_command' => 'TOKENIZATION',
            'merchant_identifier' => $this->intent->merchant_identifier,
            'access_code' => $this->intent->access_code,
            'language' => $this->intent->language,
            'merchant_reference' => (string)rand(0, getrandmax()),
            'token_name' => 'ajar-pay-' . time(),
            'return_url' => 'https://71048e0e.ngrok.io/authorization_response.php',
        ]);

        $this->assertArrayHasKey('service_command', $input);
        $this->assertArrayHasKey('merchant_reference', $input);
        $this->assertArrayHasKey('token_name', $input);
        $this->assertArrayHasKey('return_url', $input);
        $this->assertArrayHasKey('access_code', $input);
        $this->assertArrayHasKey('merchant_identifier', $input);
        $this->assertArrayHasKey('language', $input);
        $this->assertArrayHasKey('signature', $input);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->intent = $this->app->make('payfort')->configure(
            array_merge(config('payfort'), [
                'payment_method' => 'merchant_page_2',
            ])
        );
    }
}
