<?php


namespace Tests\Unit;

use Exception;
use Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use MoeenBasra\Payfort\Services\HttpClient;
use Illuminate\Validation\ValidationException;
use MoeenBasra\Payfort\PayfortFacade as Payfort;
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
    public function test_tokenization_process()
    {
        $content = $this->getContent('tokenization');
        $mock = $this->mock(HttpClient::class);

        $mock->shouldReceive('createToken')
            ->andReturn($content);

        /** @var HttpClient $client */
        $client = app(HttpClient::class);

        $data = $this->intent->prepareTokenizationData([
            'merchant_reference' => Arr::get($content, 'merchant_reference'),
            'token_name' => Arr::get($content, 'token_name'),
            'card_holder_name' => 'Moeen',
            'card_number' => '4557012345678902',
            'card_security_code' => '123',
            'expiry_date' => '2105',
            'return_url' => 'https://localhost:8088/authorization_response.php',
        ]);

        $response = $client->createToken($data);

        $this->assertEquals($response, $content);
    }

    public function test_authorization_process()
    {
        $content = $this->getContent('authorization');
        $mock = $this->mock(HttpClient::class);

        $mock->shouldReceive('authorizeTransaction')
            ->andReturn($content);

        /** @var HttpClient $client */
        $client = app(HttpClient::class);

        $response = $client->authorizeTransaction([
            'merchant_reference' => Arr::get($content, 'merchant_reference'),
            'token_name' => Arr::get($content, 'token_name'),
            'amount' => '30000',
            'customer_email' => 'example@example.net',
            'customer_ip' => '127.0.0.1',
        ]);

        $this->assertEquals($response, $content);
    }

    public function test_check_status_process()
    {
        $content = $this->getContent('check_status');

        $mock = $this->mock(HttpClient::class);

        $mock->shouldReceive('authorizeTransaction')
            ->andReturn($content);

        /** @var HttpClient $client */
        $client = app(HttpClient::class);

        $response = $client->checkStatus([
            'merchant_reference' => Arr::get($content, 'merchant_reference'),
            'token_name' => Arr::get($content, 'token_name'),
            'amount' => '30000',
            'customer_email' => 'example@example.net',
            'customer_ip' => '127.0.0.1',
        ]);

        $this->assertEquals($response, $content);
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
            'return_url' => 'https://localhost:8088/authorization_response.php',
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

    public function test_tokenization_validation()
    {
        try {
            $this->intent->prepareTokenizationData([
                'service_command' => null,
                'merchant_identifier' => null,
                'access_code' => null,
                'language' => null,
                'merchant_reference' => null,
                'token_name' => null,
                'return_url' => null,
            ]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('service_command', $e->errors());
            $this->assertArrayHasKey('merchant_identifier', $e->errors());
            $this->assertArrayHasKey('access_code', $e->errors());
            $this->assertArrayHasKey('language', $e->errors());
            $this->assertArrayHasKey('merchant_reference', $e->errors());
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->intent = Payfort::configure(
            array_merge(config('payfort'), [
                'payment_method' => 'merchant_page_2',
            ])
        );
    }

    private function getContent(string $key)
    {
        $json = json_decode(File::get(__DIR__ . '/stubs/responses.json'), true);

        return $json[$key];
    }
}
