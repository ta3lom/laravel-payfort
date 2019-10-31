<?php


namespace MoeenBasra\Payfort;

use Illuminate\Support\Facades\Facade;

/**
 * Class PayfortFacade
 *
 * @package MoeenBasra\Payfort
 *
 * @method static resolve(array $config)
 * @method static
 */
class PayfortFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'payfort';
    }
}
