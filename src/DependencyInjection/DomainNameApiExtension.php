<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DomainNameApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Parámetros
        $container->setParameter('domainname_api.provider', $config['provider'] ?? 'rest');
        $container->setParameter('domainname_api.api_key', $config['api_key'] ?? '');
        $container->setParameter('domainname_api.reseller_id', $config['reseller_id'] ?? '');
        $container->setParameter('domainname_api.rest.endpoint', $config['rest']['endpoint'] ?? 'https://api.domainresellerapi.com');
        $container->setParameter('domainname_api.rest.verify_ssl', $config['rest']['verify_ssl'] ?? true);
        $container->setParameter('domainname_api.rest.timeout', $config['rest']['timeout'] ?? 30);
        $container->setParameter('domainname_api.rest.retry_attempts', $config['rest']['retry_attempts'] ?? 3);
        $container->setParameter('domainname_api.username', $config['username'] ?? '');
        $container->setParameter('domainname_api.password', $config['password'] ?? '');
        $container->setParameter('domainname_api.test_mode', $config['test_mode'] ?? false);
        $container->setParameter('domainname_api.default_nameservers', $config['default_nameservers'] ?? ['ns1.domainnameapi.com', 'ns2.domainnameapi.com']);

        // RestClient
        $restClient = new Definition('Estratos\DomainNameApi\Infrastructure\Http\Client\RestClient');
        $restClient->setArguments([
            '$apiKey' => '%domainname_api.api_key%',
            '$resellerId' => '%domainname_api.reseller_id%',
            '$endpoint' => '%domainname_api.rest.endpoint%',
            '$verifySsl' => '%domainname_api.rest.verify_ssl%',
            '$timeout' => '%domainname_api.rest.timeout%',
            '$retryAttempts' => '%domainname_api.rest.retry_attempts%',
        ]);
        $container->setDefinition('Estratos\DomainNameApi\Infrastructure\Http\Client\RestClient', $restClient);

        // RestProvider
        $restProvider = new Definition('Estratos\DomainNameApi\Infrastructure\Provider\RestProvider');
        $restProvider->setArguments([
            '$client' => new Reference('Estratos\DomainNameApi\Infrastructure\Http\Client\RestClient'),
            '$config' => ['default_nameservers' => '%domainname_api.default_nameservers%'],
        ]);
        $container->setDefinition('Estratos\DomainNameApi\Infrastructure\Provider\RestProvider', $restProvider);

        // Alias del provider
        $providerService = $config['provider'] === 'rest'
            ? 'Estratos\DomainNameApi\Infrastructure\Provider\RestProvider'
            : 'Estratos\DomainNameApi\Infrastructure\Provider\SoapProvider';
        
        $container->setAlias('Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface', $providerService)->setPublic(true);
        $container->setParameter('domainname_api.provider_service', $providerService);

        // Casos de uso
        $useCases = [
            'CheckDomainUseCase' => ['$provider' => new Reference('Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface')],
            'GetBalanceUseCase' => ['$provider' => new Reference('Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface')],
            'ListDomainsUseCase' => ['$provider' => new Reference('Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface')],
            'GetTldsUseCase' => ['$provider' => new Reference('Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface')],
            'RegisterDomainUseCase' => [
                '$provider' => new Reference('Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface'),
                '$validator' => new Reference('validator'),
            ],
        ];

        foreach ($useCases as $name => $args) {
            $def = new Definition('Estratos\DomainNameApi\Application\UseCase\\' . $name);
            $def->setArguments($args);
            $container->setDefinition('Estratos\DomainNameApi\Application\UseCase\\' . $name, $def);
        }

        // Comandos
        $commands = [
            'CheckDomainCommand' => ['$checkDomainUseCase' => new Reference('Estratos\DomainNameApi\Application\UseCase\CheckDomainUseCase')],
            'GetBalanceCommand' => ['$getBalanceUseCase' => new Reference('Estratos\DomainNameApi\Application\UseCase\GetBalanceUseCase')],
            'ListDomainsCommand' => ['$listDomainsUseCase' => new Reference('Estratos\DomainNameApi\Application\UseCase\ListDomainsUseCase')],
            'RegisterDomainCommand' => ['$registerDomainUseCase' => new Reference('Estratos\DomainNameApi\Application\UseCase\RegisterDomainUseCase')],
            'GetTldsCommand' => ['$getTldsUseCase' => new Reference('Estratos\DomainNameApi\Application\UseCase\GetTldsUseCase')],
            'TestRestConnectionCommand' => ['$restClient' => new Reference('Estratos\DomainNameApi\Infrastructure\Http\Client\RestClient')],
        ];

        foreach ($commands as $name => $args) {
            $def = new Definition('Estratos\DomainNameApi\Command\\' . $name);
            $def->setArguments($args);
            $def->addTag('console.command');
            $container->setDefinition('Estratos\DomainNameApi\Command\\' . $name, $def);
        }

        
    }

    public function getAlias(): string
    {
        return 'domain_name_api';
    }
}
