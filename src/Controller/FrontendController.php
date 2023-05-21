<?php

namespace Sovic\Cms\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FrontendController extends AbstractController
{
    use PageControllerTrait;

    private EntityManagerInterface $entityManager;
    private array $variables = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
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
    
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        foreach ($this->variables as $key => $val) {
            $parameters[$key] = $val;
        }

        return parent::render($view, $parameters, $response);
    }

    protected function show404(): Response
    {
        $response = new Response();
        $response->setStatusCode(404);

        return $this->render('page/404.html.twig', [], $response);
    }
}
