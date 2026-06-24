<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\DTO;

/**
 * Colección paginada de dominios.
 */
class DomainCollection
{
    /**
     * @param DomainDto[] $domains
     */
    public function __construct(
        public array $domains = [],
        public int $total = 0,
        public int $page = 1,
        public int $limit = 50,
    ) {
    }

    public function getDomains(): array
    {
        return $this->domains;
    }

    public function getTotalPages(): int
    {
        if ($this->limit <= 0) {
            return 0;
        }
        return (int) ceil($this->total / $this->limit);
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->getTotalPages();
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }
}  