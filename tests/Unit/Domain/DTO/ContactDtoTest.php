<?php

declare(strict_types=1);

namespace Estratos\DomainNameApi\Tests\Unit\Domain\DTO;

use Estratos\DomainNameApi\Domain\DTO\ContactDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ContactDtoTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidContact(): void
    {
        $contact = new ContactDto(
            firstName: 'John',
            lastName: 'Doe',
            company: 'Example Inc.',
            email: 'john@example.com',
            addressLine1: '123 Main St',
            city: 'New York',
            state: 'NY',
            country: 'US',
            zipCode: '10001',
            phone: '+1234567890'
        );

        $errors = $this->validator->validate($contact);
        $this->assertCount(0, $errors);
    }

    public function testInvalidEmail(): void
    {
        $contact = new ContactDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'invalid-email',
            addressLine1: '123 Main St',
            city: 'New York',
            state: 'NY',
            country: 'US',
            zipCode: '10001',
            phone: '+1234567890'
        );

        $errors = $this->validator->validate($contact);
        $this->assertGreaterThan(0, $errors->count());
    }

    public function testGetFullName(): void
    {
        $contact = new ContactDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            addressLine1: '123 Main St',
            city: 'New York',
            state: 'NY',
            country: 'US',
            zipCode: '10001',
            phone: '+1234567890'
        );

        $this->assertEquals('John Doe', $contact->getFullName());
    }

    public function testGetFullAddress(): void
    {
        $contact = new ContactDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            addressLine1: '123 Main St',
            addressLine2: 'Apt 4B',
            city: 'New York',
            state: 'NY',
            country: 'US',
            zipCode: '10001',
            phone: '+1234567890'
        );

        $this->assertEquals('123 Main St, Apt 4B', $contact->getFullAddress());
    }
}