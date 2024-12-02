<?php

declare(strict_types = 1);

use Classes\Http\Kernel;
use Classes\Http\Request;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

define('BASE_PATH', dirname(__DIR__));


require_once BASE_PATH . '/vendor/autoload.php';

//$whoops = new Run();
//$whoops->pushHandler(new PrettyPageHandler());
//$whoops->pushHandler(new JsonResponseHandler());
//$whoops->register();

$container = require_once BASE_PATH . "/Classes/Container/Container.php";

$request = Request::createFromGlobals();

$kernel = $container->get(Kernel::class);

$response = $kernel->handle($request);

$response->send();
