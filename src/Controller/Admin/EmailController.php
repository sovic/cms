<?php

namespace Sovic\Cms\Controller\Admin;

use Sovic\Cms\Controller\Admin\Trait\EmailControllerTrait;
use Sovic\Cms\Controller\Trait\ControllerAccessTrait;
use Sovic\Common\Controller\Trait\BaseControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailController extends AbstractController
{
    use BaseControllerTrait;
    use ControllerAccessTrait;
    use EmailControllerTrait;
}
