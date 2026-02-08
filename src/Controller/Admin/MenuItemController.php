<?php

namespace Sovic\Cms\Controller\Admin;

use Sovic\Cms\Controller\Admin\Trait\MenuItemControllerTrait;
use Sovic\Cms\Controller\ProjectBaseController;
use Sovic\Common\Controller\Trait\BaseControllerTrait;

class MenuItemController extends ProjectBaseController
{
    use BaseControllerTrait;
    use MenuItemControllerTrait;
}
