<?php

namespace Classes\Http;

use Classes\Routing\RouterInterface;
use DirectoryIterator;
use Exception;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

class Kernel
{
    private string $appEnv;

    private RouterInterface $router;

    private Container $container;

    public function __construct(RouterInterface $router, Container $container)
    {
        $this->container = $container;
        $this->router = $router;

        $this->loadProvider();

        $this->appEnv = $this->container->get('APP_ENV');
    }

    public function handle(Request $request): Response
    {
        try {
            [$routeHandler, $vars] = $this->router->dispatch($request, $this->container);
            $response = call_user_func_array($routeHandler, $vars);
        } catch (Exception $exception) {
            $response = $this->createExceptionResponse($exception);
        }

        return $response;
    }

    private function createExceptionResponse(Exception $exception): Response
    {
        if (in_array($this->appEnv, ['dev', 'test'])) {
            throw $exception;
        }

        if ($exception instanceof HttpException) {
            return new Response($exception->getMessage(), $exception->getStatusCode());
        }

        return new Response(Response::HTTP_INTERNAL_SERVER_ERROR . " - Server error", Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function loadProvider(): void
    {
        $loaderFiles = new DirectoryIterator(BASE_PATH . '/Session');

        foreach ($loaderFiles as $file) {
            if (!$file->isDot() && $file->getExtension() === 'php') {
                $className = $file->getBasename('.php');
                $classFullname = 'Session\\' . $className;

                $this->container->get($classFullname)->run();
            }
        }
    }
}
