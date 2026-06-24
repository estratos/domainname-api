<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('domain_name_api');

        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                // Provider selection
                ->enumNode('provider')
                    ->values(['soap', 'rest'])
                    ->defaultValue('rest')
                    ->info('Provider to use (soap or rest)')
                ->end()

                // REST API Configuration
                ->scalarNode('api_key')
                    ->defaultValue('%env(DOMAINNAME_API_KEY)%')
                    ->info('REST API Key')
                ->end()
                ->scalarNode('reseller_id')
                    ->defaultValue('%env(DOMAINNAME_RESELLER_ID)%')
                    ->info('Reseller ID for REST API')
                ->end()
                ->arrayNode('rest')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('endpoint')
                            ->defaultValue('%env(DOMAINNAME_API_ENDPOINT)%')
                            ->info('REST API endpoint')
                        ->end()
                        ->booleanNode('verify_ssl')
                            ->defaultTrue()
                        ->end()
                        ->integerNode('timeout')
                            ->defaultValue(30)
                            ->min(1)
                            ->max(120)
                        ->end()
                        ->integerNode('retry_attempts')
                            ->defaultValue(3)
                            ->min(0)
                            ->max(10)
                        ->end()
                    ->end()
                ->end()

                // SOAP API Configuration (Legacy)
                ->scalarNode('username')
                    ->defaultValue('%env(DOMAINNAME_API_USERNAME)%')
                    ->info('SOAP API username')
                ->end()
                ->scalarNode('password')
                    ->defaultValue('%env(DOMAINNAME_API_PASSWORD)%')
                    ->info('SOAP API password')
                ->end()
                ->booleanNode('test_mode')
                    ->defaultFalse()
                    ->info('Enable test mode for SOAP')
                ->end()

                // General configuration
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

        return $treeBuilder;
    }
}