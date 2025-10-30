<?php

namespace Estratos\DomainNameApi;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DomainNameApiBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        // Configurar parámetros
        $builder->setParameter('domainname_api.username', $config['username']);
        $builder->setParameter('domainname_api.password', $config['password']);
        $builder->setParameter('domainname_api.test_mode', $config['test_mode']);
        $builder->setParameter('domainname_api.timeout', $config['timeout']);

        // Definir servicio principal con parámetros
        $container->services()
            ->get(Estratos\DomainNameApi\Service\DomainNameApiClient::class)
            ->arg('$username', $config['username'])
            ->arg('$password', $config['password'])
            ->arg('$testMode', $config['test_mode']);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}