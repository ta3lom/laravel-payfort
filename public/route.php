<?php

require __DIR__ . '/../vendor/autoload.php';
require_once 'Controller.php';
$config = require __DIR__ . '/../config/payfort.php';

use MoeenBasra\Payfort\Payfort;
use Illuminate\Support\Arr;


$controller = new Controller(new Payfort($config));

$params = array_merge($_REQUEST, $_GET);
$method = Arr::get($params, 'r');
unset($params['r']);

if ($method && method_exists($controller, $method)) {
    call_user_func_array(
        [$controller, $method],
        compact('params')
    );
} else {
    echo 'Page Not Found!';
    exit;
}




