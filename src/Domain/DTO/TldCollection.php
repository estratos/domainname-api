<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\DTO;

/**
 * Colección de TLDs disponibles.
 */
class TldCollection
{
    /**
     * @param array<int, array{
     *     tld: string,
     *     minRegistrationPeriod?: int,
     *     maxRegistrationPeriod?: int,
     *     registrationPeriods?: int[],
     *     prices?: array
     * }> $tlds
     */
    public function __construct(
        public array $tlds = [],
        public int $total = 0,
    ) {
    }

    public function getTlds(): array
    {
        return $this->tlds;
    }

    public function getTldNames(): array
    {
        return array_column($this->tlds, 'tld');
    }

    public function hasTld(string $tld): bool
    {
        return in_array($tld, $this->getTldNames(), true);
    }

    public function getTldInfo(string $tld): ?array
    {
        foreach ($this->tlds as $tldInfo) {
            if ($tldInfo['tld'] === $tld) {
                return $tldInfo;
            }
        }
        return null;
    }
}