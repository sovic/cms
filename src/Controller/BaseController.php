<?php

namespace Sovic\Cms\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Trait\PageControllerTrait;
use Sovic\Common\Controller\Trait\BaseControllerTrait;
use Sovic\Common\Project\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    use BaseControllerTrait;
    use PageControllerTrait;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Settings               $settings,
    ) {
    }
}
