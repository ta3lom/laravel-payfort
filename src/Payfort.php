<?php


namespace MoeenBasra\Payfort;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MoeenBasra\Payfort\MerchantPage\MerchantPage;
use MoeenBasra\Payfort\Exceptions\PayfortException;

class Payfort
{
    /** @var MerchantPage */
    protected $payment_method;

    public function __construct(array $config)
    {
        $this->resolve($config);
    }

    /**
     * resolve the payment payment_method
     *
     * @param array $config
     *
     * @throws PayfortException
     */
    private function resolve(array $config): void
    {
        $method = 'configure' . (Str::studly(Arr::get($config, 'payment_method'))) . 'Method';

        if (!method_exists($this, $method)) {
            throw new PayfortException(sprintf('Oops, no payment method register with "%s" name', $method));
        }

        $this->payment_method = call_user_func_array(
            [$this, $method],
            compact('config')
        );
    }

    /**
     * configure the payfort
     *
     * @param array $config
     *
     * @return $this
     * @throws PayfortException
     */
    public function configure(array $config): self
    {
        $this->resolve($config);

        return $this;
    }

    /**
     * @param array $config
     *
     * @throws \MoeenBasra\Payfort\Exceptions\PayfortException
     *
     * //@todo: yet to implement
     */
    private function configureMerchantPageMethod(array $config)
    {
        throw new PayfortException('Sorry, the method "merchant page" is not available yet');

    }

    /**
     * get the merchant page 2 payment_method
     *
     * @param array $config
     *
     * @return \MoeenBasra\Payfort\MerchantPage\MerchantPage
     */
    private function configureMerchantPage2Method(array $config)
    {
        return new MerchantPage($config);
    }

    /**
     * @param array $config
     *
     * @throws \MoeenBasra\Payfort\Exceptions\PayfortException
     *
     * //@todo: yet to implement
     */
    private function configureRedirectionMethod(array $config)
    {
        throw new PayfortException('Sorry, the method "redirection" is not available yet');
    }

    /**
     * @param array $config
     *
     * @throws \MoeenBasra\Payfort\Exceptions\PayfortException
     * @todo: yet to implement
     */
    private function configureSadadMethod(array $config)
    {
        throw new PayfortException('Sorry, the method "sadad" is not available yet');
    }

    /**
     * @param array $config
     *
     * @throws \MoeenBasra\Payfort\Exceptions\PayfortException
     * @todo: yet to implement
     */
    private function configureNapsMethod(array $config)
    {
        throw new PayfortException('Sorry, the method "naps" is not available yet');
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return $this->payment_method->$name;
    }

    public function __set($name, $value)
    {
        $this->payment_method->$name = $value;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(
            [$this->payment_method, $name],
            $arguments
        );
    }
}
