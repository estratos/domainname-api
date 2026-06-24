<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DomainNameApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Cargar servicios
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        // Set parameters
        $container->setParameter('domainname_api.provider', $config['provider']);
        
        // REST parameters
        $container->setParameter('domainname_api.api_key', $config['api_key']);
        $container->setParameter('domainname_api.reseller_id', $config['reseller_id']);
        $container->setParameter('domainname_api.rest.endpoint', $config['rest']['endpoint']);
        $container->setParameter('domainname_api.rest.verify_ssl', $config['rest']['verify_ssl']);
        $container->setParameter('domainname_api.rest.timeout', $config['rest']['timeout']);
        $container->setParameter('domainname_api.rest.retry_attempts', $config['rest']['retry_attempts']);
        
        // SOAP parameters (legacy)
        $container->setParameter('domainname_api.username', $config['username']);
        $container->setParameter('domainname_api.password', $config['password']);
        $container->setParameter('domainname_api.test_mode', $config['test_mode']);
        
        // General parameters
        $container->setParameter('domainname_api.timeout', $config['timeout']);
        $container->setParameter('domainname_api.default_nameservers', $config['default_nameservers']);

        // Set provider service alias
        $providerService = $config['provider'] === 'rest'
            ? 'Estratos\DomainNameApi\Infrastructure\Provider\RestProvider'
            : 'Estratos\DomainNameApi\Infrastructure\Provider\SoapProvider';
        $container->setParameter('domainname_api.provider_service', $providerService);
    }

    public function getAlias(): string
    {
        return 'domain_name_api';
    }
}