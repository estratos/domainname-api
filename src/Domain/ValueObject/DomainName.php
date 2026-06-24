<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\ValueObject;

use Estratos\DomainNameApi\Domain\Exception\DomainException;

/**
 * Value Object para nombres de dominio.
 * Inmutable y con validación.
 */
class DomainName
{
    private string $domain;
    private string $name;
    private string $tld;
    private ?string $subdomain;

    public function __construct(string $domain)
    {
        $this->validate($domain);
        $this->domain = strtolower(trim($domain));
        $this->parseDomain();
    }

    private function validate(string $domain): void
    {
        // Validación básica de formato
        if (!preg_match('/^([a-zA-Z0-9-]+)\.([a-zA-Z]{2,})$/', $domain)) {
            throw new DomainException(sprintf('Invalid domain name format: "%s"', $domain));
        }

        // Validación de longitud máxima
        if (strlen($domain) > 253) {
            throw new DomainException(sprintf('Domain name too long: "%s" (max 253 characters)', $domain));
        }

        // Validación de caracteres permitidos
        if (!preg_match('/^[a-zA-Z0-9.-]+$/', $domain)) {
            throw new DomainException(sprintf('Domain name contains invalid characters: "%s"', $domain));
        }
    }

    private function parseDomain(): void
    {
        $parts = explode('.', $this->domain);
        $this->name = $parts[0];
        $this->tld = implode('.', array_slice($parts, 1));

        // Verificar si tiene subdominio
        if (strpos($this->name, '.') !== false) {
            $subParts = explode('.', $this->name);
            $this->subdomain = implode('.', array_slice($subParts, 0, -1));
            $this->name = end($subParts);
        } else {
            $this->subdomain = null;
        }
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTld(): string
    {
        return $this->tld;
    }

    public function getSubdomain(): ?string
    {
        return $this->subdomain;
    }

    public function hasSubdomain(): bool
    {
        return $this->subdomain !== null;
    }

    public function getFullName(): string
    {
        if ($this->subdomain) {
            return $this->subdomain . '.' . $this->name;
        }
        return $this->name;
    }

    public function isIdn(): bool
    {
        return preg_match('/[^\x00-\x7F]/', $this->domain) === 1;
    }

    public function toPunycode(): string
    {
        if (function_exists('idn_to_ascii')) {
            return idn_to_ascii($this->domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) ?: $this->domain;
        }
        return $this->domain;
    }

    public function __toString(): string
    {
        return $this->domain;
    }

    public function equals(DomainName $other): bool
    {
        return $this->domain === $other->getDomain();
    }
}