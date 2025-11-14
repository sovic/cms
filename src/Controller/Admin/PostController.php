<?php

namespace Sovic\Cms\Controller\Admin;

use Sovic\Cms\Controller\Admin\Trait\PostControllerTrait;
use Sovic\Cms\Controller\ProjectBaseController;
use Sovic\Common\Controller\Trait\BaseControllerTrait;

class PostController extends ProjectBaseController
{
    use BaseControllerTrait;
    use PostControllerTrait;
}
