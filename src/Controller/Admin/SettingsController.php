<?php

namespace Sovic\Cms\Controller\Admin;

use Sovic\Cms\Controller\Admin\Trait\SettingsControllerTrait;
use Sovic\Common\Controller\Trait\BaseControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsController extends AbstractController
{
    use BaseControllerTrait;
    use SettingsControllerTrait;
}
