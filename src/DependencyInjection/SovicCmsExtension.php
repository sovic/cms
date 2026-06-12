<?php

namespace Sovic\Cms\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class SovicCmsExtension extends Extension
{
    public function getAlias(): string
    {
        return 'sovic_cms';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('base_gallery_url', $config['base_gallery_url']);
        $container->setParameter('base_public_url', $config['base_public_url']);
        $container->setParameter('tinymce_content_css', $config['tinymce_content_css']);
        $container->setParameter('page_enable_tags', $config['page']['enable_tags']);
    }
}
