<?php


namespace MoeenBasra\Payfort\Exceptions;

use Throwable;

class IncompletePayment extends PayfortException
{
    public function __construct($message = '', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
