<?php

namespace Sovic\Cms\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class SovicCmsExtension extends Extension
{
    public function getAlias(): string
    {
        return 'sovic_cms';
    }

    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('base_gallery_url', $config['base_gallery_url']);
        $container->setParameter('base_public_url', $config['base_public_url']);
        $container->setParameter('tinymce_content_css', $config['tinymce_content_css']);
        $container->setParameter('page_enable_tags', $config['page']['enable_tags']);

        $ai = $config['ai'];
        $container->setParameter('ai_components_dir', $ai['components_dir']);
        $container->setParameter('ai_styleguide_file', $ai['styleguide_file']);
        $container->setParameter('ai_model', $ai['model']);
        $container->setParameter('ai_max_tokens', $ai['max_tokens']);
        $container->setParameter('ai_history_limit', $ai['history_limit']);
        $container->setParameter('ai_enabled', !empty($ai['components_dir']));

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');
    }
}
