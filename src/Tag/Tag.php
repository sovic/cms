<?php

namespace Sovic\Cms\Tag;

use Sovic\Cms\Model\Trait\PrivateSlugTrait;
use Sovic\Common\Model\AbstractEntityModel;

/**
 * @property \Sovic\Cms\Entity\Tag $entity
 */
class Tag extends AbstractEntityModel
{
    use PrivateSlugTrait;
}
