<?php

namespace Estratos\DomainNameApi\Service;

/**
 * Utilidades para DomainName API
 */
class DomainNameApiUtils
{
    /**
     * Extrae el TLD de un dominio
     */
    public static function extractTld(string $domain): string
    {
        $parts = explode('.', $domain);
        return end($parts);
    }

    /**
     * Valida el formato de un dominio
     */
    public static function isValidDomain(string $domain): bool
    {
        return preg_match('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i', $domain) === 1;
    }

    /**
     * Formatea información de contacto para la API
     */
    public static function formatContactInfo(array $contact): array
    {
        return [
            'FirstName' => $contact['firstName'] ?? $contact['FirstName'] ?? '',
            'LastName' => $contact['lastName'] ?? $contact['LastName'] ?? '',
            'Company' => $contact['company'] ?? $contact['Company'] ?? '',
            'EMail' => $contact['email'] ?? $contact['EMail'] ?? '',
            'AddressLine1' => $contact['address1'] ?? $contact['AddressLine1'] ?? '',
            'City' => $contact['city'] ?? $contact['City'] ?? '',
            'State' => $contact['state'] ?? $contact['State'] ?? '',
            'Country' => $contact['country'] ?? $contact['Country'] ?? 'US',
            'ZipCode' => $contact['zipCode'] ?? $contact['ZipCode'] ?? '',
            'Phone' => $contact['phone'] ?? $contact['Phone'] ?? '',
            'PhoneCountryCode' => $contact['phoneCountryCode'] ?? $contact['PhoneCountryCode'] ?? '1',
        ];
    }

    /**
     * Crea una estructura de contactos completa
     */
    public static function createContactStructure(array $registrant, ?array $admin = null, ?array $tech = null, ?array $billing = null): array
    {
        $admin = $admin ?? $registrant;
        $tech = $tech ?? $registrant;
        $billing = $billing ?? $registrant;

        return [
            'Registrant' => self::formatContactInfo($registrant),
            'Administrative' => self::formatContactInfo($admin),
            'Technical' => self::formatContactInfo($tech),
            'Billing' => self::formatContactInfo($billing),
        ];
    }

    /**
     * Parsea fechas de la API a objetos DateTime
     */
    public static function parseApiDate(?string $dateString): ?\DateTime
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            return new \DateTime($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Valida y normaliza un array de nameservers
     */
    public static function normalizeNameservers(array $nameservers): array
    {
        $normalized = [];
        foreach ($nameservers as $ns) {
            $ns = trim($ns);
            if (!empty($ns) && preg_match('/^[a-z0-9.-]+$/i', $ns)) {
                $normalized[] = $ns;
            }
        }
        return array_slice($normalized, 0, 5); // Máximo 5 nameservers
    }

    /**
     * Genera un auth code aleatorio (para transferencias)
     */
    public static function generateAuthCode(int $length = 16): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $authCode = '';
        $max = strlen($characters) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $authCode .= $characters[random_int(0, $max)];
        }
        
        return $authCode;
    }
}
