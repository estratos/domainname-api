<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Domain\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO para datos de contacto.
 */
class ContactDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 80)]
        public string $firstName,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 80)]
        public string $lastName,

        #[Assert\Length(max: 256)]
        public string $company = '',

        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 256)]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 4, max: 256)]
        public string $addressLine1,

        #[Assert\Length(max: 256)]
        public string $addressLine2 = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 80)]
        public string $city,

        #[Assert\Length(max: 80)]
        public string $state = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 2)]
        #[Assert\Country]
        public string $country,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 20)]
        public string $zipCode,

        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\+?[0-9]{8,15}$/')]
        public string $phone,

        #[Assert\Length(max: 20)]
        public string $fax = '',

        #[Assert\Length(max: 3)]
        public string $phoneCountryCode = '',
    ) {
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getFullAddress(): string
    {
        $address = $this->addressLine1;
        if ($this->addressLine2) {
            $address .= ', ' . $this->addressLine2;
        }
        return $address;
    }
}