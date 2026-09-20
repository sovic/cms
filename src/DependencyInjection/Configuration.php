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
                ->arrayNode('page')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enable_tags')
                            ->defaultFalse()
                            ->info('Show the tag field in the page edit form and the tags content type option.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('ai')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('components_dir')
                            ->defaultNull()
                            ->info('Directory with HTML component snippets, one *.html file per component (file name = component name). AI assistant is enabled when set.')
                        ->end()
                        ->scalarNode('styleguide_file')
                            ->defaultNull()
                            ->info('Markdown or HTML file describing the website styleguide.')
                        ->end()
                        ->scalarNode('model')
                            ->defaultValue('claude-opus-5')
                            ->info('Default Anthropic model, users can override it in their AI settings.')
                        ->end()
                        ->integerNode('max_tokens')
                            ->defaultValue(16000)
                            ->min(1024)
                            ->info('Max output tokens of one assistant response.')
                        ->end()
                        ->integerNode('history_limit')
                            ->defaultValue(20)
                            ->min(0)
                            ->info('Max number of previous conversation messages sent to the model.')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
