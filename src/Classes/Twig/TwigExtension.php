<?php

namespace Classes\Twig;

use Classes\SessionHandler;
use Helpers\StringHelper;
use League\Container\Container;
use Services\DossierService;
use Services\StationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

require_once __DIR__ . '/../../public/dbClasses/station.php';

class TwigExtension extends AbstractExtension
{
    private string $publicFolder;

    private SessionHandler $session;

    private Container $container;

    public function __construct(string $publicFolder, Container $container)
    {
        $this->publicFolder = $publicFolder;
        $this->container = $container;
        $this->session = $this->container->get(SessionHandler::class);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cache', [$this, 'cache']),
            new TwigFunction('asset', [$this, 'asset']),
            new TwigFunction('session', [$this, 'session']),
            new TwigFunction('env', [$this, 'env']),
            new TwigFunction('isAuthorized', [$this, 'isAuthorized']),
            new TwigFunction('infos', [$this, 'infos']),
            new TwigFunction('modules', [$this, 'modules']),
            new TwigFunction('setStyles', [$this, 'setStyles']),
            new TwigFunction('getAllExerciceByStation', [$this, 'getAllExerciceByStation']),
            new TwigFunction('getBalanceImportByDossier', [$this, 'getBalanceImportByDossier']),
        ];
    }

    public function cache(string $filePath): string
    {
        return str_replace("app/", "", $filePath);
        return StringHelper::add_version(str_replace("app/", "", $filePath));
    }

    public function asset(string $filePath): string
    {
        return $this->publicFolder . '/' . ltrim($filePath, '/');
    }

    public function session(array $key): string
    {
        return $this->session->get($key);
    }

    public function env(string $key): string
    {
        return getenv($key);
    }

    public function isAuthorized(string $key): bool
    {
        return $this->session->get(['auth', $key]);
    }

    public function infos(string $key): string
    {
        return $this->session->get(['infos', $key]);
    }

    public function setStyles(): string
    {
        return file_get_contents($this->publicFolder . '/style.css');
    }

    public function getAllExerciceByStation(): array
    {
        return $this->container->get(StationService::class)->getAllExerciceByStation();
    }

    public function getBalanceImportByDossier(): array
    {
        return $this->container->get(DossierService::class)->getBalanceImportByDossier();
    }
}
