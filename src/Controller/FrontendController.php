<?php

namespace SovicCms\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FrontendController extends AbstractController
{
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

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        foreach ($this->variables as $key => $val) {
            $parameters[$key] = $val;
        }

        return parent::render($view, $parameters, $response);
    }
}
