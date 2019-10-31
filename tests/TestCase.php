<?php


namespace Tests;

use MoeenBasra\Payfort\PayfortFacade;
use MoeenBasra\Payfort\PayfortServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var array
     */
    protected $config;

    protected $intent;

    protected function getPackageProviders($app)
    {
        return [PayfortServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Payfort' => PayfortFacade::class
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = config('payfort');
    }
}
