<?php


namespace Tests\Unit;

use Tests\TestCase;
use MoeenBasra\Payfort\PayfortFacade as Payfort;
use MoeenBasra\Payfort\MerchantPage\MerchantPage;

class PaymentMethodTest extends TestCase
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
        $signature = $this->createSignature();
        // create signature
        $signature2 = $this->intent->createSignature($this->config);

        $this->assertEquals($signature, $signature2);
    }

    /**
     * create signature
     *
     * @return string
     */
    private function createSignature(): string
    {
        $input = $this->config;
        $string = '';

        // sort array with keys asc
        ksort($input);

        foreach ($input as $k => $v) {
            $string .= "$k=$v";
        }

        // pre and post fix the string
        $string = $this->config['sha_request_phrase'] . $string . $this->config['sha_request_phrase'];

        // create a hash for string
        return hash($this->config['encryption'], $string);
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
}
