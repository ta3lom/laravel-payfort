<?php


namespace MoeenBasra\Payfort\Exceptions;

use Exception;
use Throwable;

class PayfortException extends Exception
{
    public function __construct(
        $message = "Oops, something went wrong with payfort",
        $code = 400,
        Throwable $previous = null
    ) {
        $this->message = $message;
        $this->code = $code;

        parent::__construct($this->message, $this->code, $previous);
    }
}
