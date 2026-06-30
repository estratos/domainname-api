<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DomainNameApiBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Cargar servicios
        $container->import(__DIR__ . '/Resources/config/services.yaml');

        // Configurar parámetros
        $builder->setParameter('domainname_api.provider', $config['provider'] ?? 'rest');
        $builder->setParameter('domainname_api.api_key', $config['api_key'] ?? '');
        $builder->setParameter('domainname_api.reseller_id', $config['reseller_id'] ?? '');
        
        // REST config
        $builder->setParameter('domainname_api.rest.endpoint', $config['rest']['endpoint'] ?? 'https://api.domainresellerapi.com');
        $builder->setParameter('domainname_api.rest.verify_ssl', $config['rest']['verify_ssl'] ?? true);
        $builder->setParameter('domainname_api.rest.timeout', $config['rest']['timeout'] ?? 30);
        $builder->setParameter('domainname_api.rest.retry_attempts', $config['rest']['retry_attempts'] ?? 3);
        
        // SOAP config (legacy)
        $builder->setParameter('domainname_api.username', $config['username'] ?? '');
        $builder->setParameter('domainname_api.password', $config['password'] ?? '');
        $builder->setParameter('domainname_api.test_mode', $config['test_mode'] ?? false);
        
        // General
        $builder->setParameter('domainname_api.timeout', $config['timeout'] ?? 30);
        $builder->setParameter('domainname_api.default_nameservers', $config['default_nameservers'] ?? ['ns1.domainnameapi.com', 'ns2.domainnameapi.com']);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
