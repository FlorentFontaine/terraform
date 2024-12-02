<?php

namespace Classes\Container\Providers;

use Classes\SessionHandler;
use Classes\Twig\TwigExtension;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class TwigServiceProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        $services = [
            'filesystem-loader',
            'twig'
        ];

        return in_array($id, $services);
    }

    public function register(): void
    {
        $this->getContainer()->addShared('filesystem-loader', FilesystemLoader::class)
            ->addArgument(new StringArgument(__DIR__ . '/../../../Templates'));

        $this->getContainer()->addShared('twig', Environment::class)
            ->addArgument('filesystem-loader')
            ->addArgument(new ArrayArgument(['debug' => true]))
            ->addMethodCall('addExtension', [new DebugExtension()])
            ->addMethodCall('addExtension', [new TwigExtension(__DIR__ . '/../../../public', $this->getContainer())]);

        // Twig globales variables
        //---------------------

        $twig = $this->getContainer()->get('twig');
        $twig->addGlobal('modules', $this->getContainer()->get(SessionHandler::class)->get(['MODULES']));
    }
}
