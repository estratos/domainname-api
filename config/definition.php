<?php

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('username')
                ->defaultValue('%env(DOMAINNAME_API_USERNAME)%')
                ->info('DomainName API username')
            ->end()
            ->scalarNode('password')
                ->defaultValue('%env(DOMAINNAME_API_PASSWORD)%')
                ->info('DomainName API password')
            ->end()
            ->booleanNode('test_mode')
                ->defaultValue('%env(bool:DOMAINNAME_API_TEST_MODE)%')
                ->info('Enable test mode')
            ->end()
            ->integerNode('timeout')
                ->defaultValue(30)
                ->min(1)
                ->info('API timeout in seconds')
            ->end()
            ->arrayNode('default_nameservers')
                ->scalarPrototype()->end()
                ->defaultValue(['ns1.domainnameapi.com', 'ns2.domainnameapi.com'])
                ->info('Default nameservers for domain registration')
            ->end()
        ->end();
};