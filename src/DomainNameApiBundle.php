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
        // Cargar servicios
        $container->import('../config/services.yaml');

        // Configurar el servicio principal con los parámetros
        $container->services()
            ->get(Estratos\DomainNameApi\Service\DomainNameApiClient::class)
            ->arg('$username', $config['username'])
            ->arg('$password', $config['password'])
            ->arg('$testMode', $config['test_mode'])
            ->arg('$config', [
                'timeout' => $config['timeout'],
                'default_nameservers' => $config['default_nameservers'],
            ]);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}