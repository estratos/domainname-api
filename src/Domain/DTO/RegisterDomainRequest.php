<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO para solicitud de registro de dominio.
 */
class RegisterDomainRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^([a-zA-Z0-9-]+)\.([a-zA-Z]{2,})$/')]
        public string $domain,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: [1, 2, 3, 5, 10])]
        public int $period = 1,

        #[Assert\Valid]
        public ContactDto $registrant,

        #[Assert\Valid]
        public ContactDto $admin,

        #[Assert\Valid]
        public ContactDto $technical,

        #[Assert\Valid]
        public ContactDto $billing,

        #[Assert\All([
            new Assert\NotBlank(),
            new Assert\Regex(pattern: '/^([a-zA-Z0-9-]+)\.([a-zA-Z]{2,})$/')
        ])]
        public array $nameservers = [],

        public bool $privacyProtection = false,

        public ?string $authCode = null,

        public ?int $autoRenew = null,

        public ?array $tldAttributes = null,

        public ?bool $useTrusteeContact = false,
    ) {
    }

    public function getDomainName(): string
    {
        return $this->domain;
    }

    public function getNameservers(): array
    {
        return $this->nameservers;
    }

    public function hasValidNameservers(): bool
    {
        return !empty($this->nameservers) && count($this->nameservers) >= 2;
    }
}