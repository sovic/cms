<?php

namespace Sovic\Cms\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    use PageControllerTrait;

    private EntityManagerInterface $entityManager;
    private array $variables = [];
    private string $locale = 'en';

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    protected function assign(string $key, mixed $val): void
    {
        $this->variables[$key] = $val;
    }

    protected function assignArray(array $data): void
    {
        foreach ($data as $key => $val) {
            $this->variables[$key] = $val;
        }
    }

    protected function getRenderParameters(array $parameters = []): array
    {
        $locale = $this->locale;
        $lang = explode('_', $locale)[0] ?? 'en';
        $parameters['lang'] = $lang;
        $parameters['locale'] = $locale;

        foreach ($this->variables as $key => $val) {
            $parameters[$key] = $val;
        }

        return $parameters;
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters = $this->getRenderParameters($parameters);

        return parent::render($view, $parameters, $response);
    }

    protected function renderView(string $view, array $parameters = []): string
    {
        $parameters = $this->getRenderParameters($parameters);

        return parent::renderView($view, $parameters);
    }

    protected function render404(string $template = 'page/404.html.twig', array $parameters = []): Response
    {
        $response = new Response();
        $response->setStatusCode(404);

        return $this->render($template, $parameters, $response);
    }
}
