<?php

namespace Sovic\Cms\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Trait\PageControllerTrait;
use Sovic\Cms\Controller\Trait\ProjectControllerTrait;
use Sovic\Common\Controller\Trait\BaseControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectBaseController extends AbstractController implements ProjectControllerInterface
{
    use BaseControllerTrait;
    use PageControllerTrait;
    use ProjectControllerTrait;

    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
    }
}
