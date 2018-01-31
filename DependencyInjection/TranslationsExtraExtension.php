<?php

namespace Lucasweb\TranslationsExtraBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class TranslationsExtraExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('translationsextra.default_format', $config['default_format']);
        $container->setParameter('translationsextra.main_folder', $config['main_folder']);
        $container->setParameter('translationsextra.default_locale', $config['default_locale']);
        $container->setParameter('translationsextra.other_locales', $config['other_locales']);
        $container->setParameter('translationsextra.domains', $config['domains']);

        if (isset($config['yandex_api_key'])) {
            $container->setParameter('translationsextra.yandex_api_key', $config['yandex_api_key']);
        } else {
            $container->setParameter('translationsextra.yandex_api_key', '');
        }

    }
}