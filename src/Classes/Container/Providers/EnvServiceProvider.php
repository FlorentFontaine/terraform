<?php

namespace Classes\Container\Providers;

use Dotenv\Dotenv;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class EnvServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    public function boot(): void
    {
        $dotEnv = new Dotenv(dirname(__DIR__). "/../../", '.env');
        $dotEnv->load();

        if (file_exists(dirname(__DIR__). "/../../" . '.env.override')) {
            $dotEnvOverride = new Dotenv(dirname(__DIR__). "/../../", '.env.override');
            $dotEnvOverride->overload();
        }
    }

    public function provides(string $id): bool
    {
        $services = [
            'APP_ENV'
        ];

        return in_array($id, $services);
    }

    public function register(): void
    {
        $this->getContainer()->add('APP_ENV', new StringArgument($_SERVER['APP_ENV']));
    }
}
