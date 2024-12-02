<?php

use Classes\Container\Providers\EnvServiceProvider;
use Classes\Http\Kernel;
use Classes\Routing\Router;
use Classes\Routing\RouterInterface;
use Classes\SessionHandler;
use Classes\Container\Providers\TwigServiceProvider;
use Controller\AbstractController;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Repositories\ModuleRepository;
use Services\AbstractService;
use Services\Modules\Commentaire\CommentaireService;
use Services\ModuleService;

$container = (new Container())->delegate(new ReflectionContainer(true));

//---------------------
// ServiceProviders
//---------------------

$container->addServiceProvider(new EnvServiceProvider);
$container->addServiceProvider(new TwigServiceProvider);

//---------------------
// Auto-wiring services
//---------------------

// Session (Singleton)
$container->addShared(SessionHandler::class);

// RouterInterface
$routes = require __DIR__ . "/../../Routes/web.php";
$container->add(RouterInterface::class, Router::class);
$container->extend(RouterInterface::class)
    ->addMethodCall('setRoutes', [new ArrayArgument($routes)]);

// Kernel
$container->add(Kernel::class)
    ->addArguments([RouterInterface::class, $container]);



// Inflectors

$container->add(AbstractController::class);
$container->inflector(AbstractController::class)
    ->invokeMethod('setContainer', [$container])
    ->invokeMethod('setSession', [SessionHandler::class]);

$container->add(AbstractService::class);
$container->inflector(AbstractService::class)
    ->invokeMethod('setSession', [SessionHandler::class]);



// Facades

// - Module
$container->add(ModuleService::class)
->addArgument(ModuleRepository::class);

return $container;
