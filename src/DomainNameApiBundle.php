<?php

namespace Estratos\DomainNameApi;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DomainNameApiBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Cargar servicios
        $container->import('../config/services.yaml');

        // Configurar parámetros
        $builder->setParameter('domainname_api.username', $config['username'] ?? '');
        $builder->setParameter('domainname_api.password', $config['password'] ?? '');
        $builder->setParameter('domainname_api.test_mode', $config['test_mode'] ?? false);
        $builder->setParameter('domainname_api.timeout', $config['timeout'] ?? 30);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}