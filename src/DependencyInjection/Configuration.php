<?php

namespace Sovic\Cms\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sovic_cms');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('base_gallery_url')
                    ->defaultValue('')
                    ->info('Base URL for public gallery files.')
                ->end()
                ->scalarNode('base_public_url')
                    ->defaultValue('')
                    ->info('Base URL of the website, used when creating links from admin.')
                ->end()
                ->scalarNode('tinymce_content_css')
                    ->defaultValue('')
                    ->info('Custom CSS file URL loaded into the TinyMCE content editor.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
