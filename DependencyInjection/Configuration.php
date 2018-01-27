<?php

namespace Lucasweb\TranslationsExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('translations_extra');

        $rootNode->children()
            ->enumNode('default_format')
            ->values(array('xml', 'yaml', 'php'))
            ->end()
            ->scalarNode('default_locale')->isRequired()->end()
            ->scalarNode('main_folder')->isRequired()->end()
            ->arrayNode('other_locales')
            ->scalarPrototype()->end()
            ->isRequired()->end()
            ->arrayNode('domains')
            ->scalarPrototype()->end()
            ->requiresAtLeastOneElement()->isRequired()->end()
            ->scalarNode('yandex_api_key')->end()
            ->end();

        return $treeBuilder;
    }
}