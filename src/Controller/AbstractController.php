<?php

namespace Controller;


use Classes\Http\Response;
use Classes\SessionHandler;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    protected ?ContainerInterface $container = null;
    protected ?SessionHandler $session = null;

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function setSession(SessionHandler $session): void
    {
        $this->session = $session;
    }

    public function render(string $template, array $parameters = [], Response $response = null): Response
    {
        $content = $this->container->get('twig')->render($template, $parameters);
        $response ??= new Response();
        $response->setContent($content);

        return $response;
    }

    public function getContent(string $template, array $parameters = []): string
    {
        return $this->container->get('twig')->render($template, $parameters);
    }

    public function redirectToUrl(string $url)
    {
        header('Location: ' . $url);
        exit;
    }

    public function responseJson(array $data): Response
    {
        $response = new Response();
        $response->setContent(json_encode($data));

        return $response;
    }
}
