<?php

declare(strict_types = 1);

use Classes\Container;
use Classes\Http\Kernel;
use Classes\Http\Request;

define('BASE_PATH', dirname(__DIR__));

require_once __DIR__ . '/../../Init/bootstrap.php';

$request = Request::createFromGlobals();

$kernel = Container::getInstance()->get(Kernel::class);

$response = $kernel->handle($request);

$response->send();
