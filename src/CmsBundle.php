<?php

namespace Sovic\Cms;

use Sovic\Cms\DependencyInjection\SovicCmsExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CmsBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new SovicCmsExtension();
    }

    public function registerCommands(Application $application): void
    {

    }
}
