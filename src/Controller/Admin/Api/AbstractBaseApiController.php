<?php

namespace Sovic\Cms\Controller\Admin\Api;

use Sovic\Cms\Controller\Admin\AdminBaseController;
use Sovic\Common\Controller\Trait\JsonRequestTrait;
use Sovic\Common\Controller\Trait\JsonResponseTrait;

abstract class AbstractBaseApiController extends AdminBaseController
{
    use JsonRequestTrait;
    use JsonResponseTrait;

    protected const DateFormat = 'Y-m-d H:i:s';
}
