# DomainNameAPI Bundle for Symfony

[![Latest Version](https://img.shields.io/packagist/v/estratos/domainname-api.svg?style=flat-square)](https://packagist.org/packages/estratos/domainname-api)
[![Total Downloads](https://img.shields.io/packagist/dt/estratos/domainname-api.svg?style=flat-square)](https://packagist.org/packages/estratos/domainname-api)
[![License](https://img.shields.io/packagist/l/estratos/domainname-api.svg?style=flat-square)](https://packagist.org/packages/estratos/domainname-api)
[![PHP Version](https://img.shields.io/packagist/php-v/estratos/domainname-api.svg?style=flat-square)](https://packagist.org/packages/estratos/domainname-api)
[![Symfony Version](https://img.shields.io/badge/symfony-5.4%20%7C%206.0%20%7C%207.0-blue)](https://symfony.com)

A comprehensive Symfony bundle for integrating DomainNameAPI / DomainResellerAPI services with REST and SOAP support.

---

## 🚀 Features

### Current (v1.0)

- ✅ **Domain Availability Check** - Check if a domain is available for registration
- ✅ **Domain Registration** - Register new domains with contact information
- ✅ **Account Balance** - Query your reseller account balance
- ✅ **Domain Listing** - List all domains in your account with pagination
- ✅ **TLD List** - Get all available TLDs with pricing information
- ✅ **REST API Support** - New REST API integration
- ✅ **SOAP API Support** - Legacy SOAP API support
- ✅ **Symfony Commands** - Full console commands for all operations
- ✅ **REST Controllers** - Ready-to-use API endpoints
- ✅ **Architecture** - Clean architecture with Ports & Adapters

### Roadmap (v1.1+)

- 🔄 Domain Renewal
- 🔄 Domain Transfer
- 🔄 WHOIS Information
- 🔄 DNS Management
- 🔄 DNSSEC Support
- 🔄 Contact Management

---

## 📋 Requirements

- PHP 8.1 or higher
- Symfony 5.4, 6.0, or 7.0
- Composer

---

## 🔧 Installation

### Step 1: Install the bundle

```bash
composer require estratos/domainname-api
```

### Step 2: Register the bundle

```php
// config/bundles.php

return [
    // ...
    Estratos\DomainNameApi\DomainNameApiBundle::class => ['all' => true],
];
```

### Step 3: Configure the bundle

```yaml
# config/packages/domain_name_api.yaml

domain_name_api:
    # Provider selection (rest | soap)
    provider: '%env(DOMAINNAME_API_PROVIDER)%'

    # REST API (recommended)
    api_key: '%env(DOMAINNAME_API_KEY)%'
    reseller_id: '%env(DOMAINNAME_RESELLER_ID)%'

    rest:
        endpoint: '%env(DOMAINNAME_API_ENDPOINT)%'
        verify_ssl: true
        timeout: 30
        retry_attempts: 3

    # SOAP API (legacy)
    username: '%env(DOMAINNAME_API_USERNAME)%'
    password: '%env(DOMAINNAME_API_PASSWORD)%'
    test_mode: '%env(bool:DOMAINNAME_API_TEST_MODE)%'

    # General
    timeout: 30

    default_nameservers:
        - 'ns1.domainnameapi.com'
        - 'ns2.domainnameapi.com'
```

### Step 4: Add environment variables

```env
###> estratos/domainname-api ###

# REST API (recommended)
DOMAINNAME_API_PROVIDER=rest
DOMAINNAME_API_KEY=your_api_key_here
DOMAINNAME_RESELLER_ID=your_reseller_id_here
DOMAINNAME_API_ENDPOINT=https://api.domainresellerapi.com

# SOAP API (legacy - optional)
DOMAINNAME_API_USERNAME=your_username
DOMAINNAME_API_PASSWORD=your_password
DOMAINNAME_API_TEST_MODE=false

###< estratos/domainname-api ###
```

---

## 🛠️ Usage

### Console Commands

```bash
# Test REST API connection
php bin/console domain:test-rest

# Check domain availability
php bin/console domain:check example.com

# Check domain with JSON output
php bin/console domain:check example.com --format=json

# Check account balance
php bin/console domain:balance

# Get balance with low balance notification
php bin/console domain:balance --notify --threshold=100

# List domains
php bin/console domain:list

# List domains with pagination
php bin/console domain:list --page=2 --limit=20

# List domains by status
php bin/console domain:list --status=active

# List available TLDs
php bin/console domain:tlds

# Search TLDs
php bin/console domain:tlds --search=.com

# Register a domain (interactive)
php bin/console domain:register example.com

# Register a domain with options
php bin/console domain:register example.com \
    --period=2 \
    --nameservers=ns1.example.com \
    --nameservers=ns2.example.com \
    --privacy \
    --autorenew
```

---

## 🌐 REST API Endpoints

Once the bundle is configured, the following endpoints are available:

```http
GET /api/domain/{domain}/check
GET /api/domain/balance
GET /api/domain/list?page=1&limit=50
GET /api/domain/tlds
POST /api/domain/register
```

---

## 📦 Example: Register Domain via API

```bash
curl -X POST http://localhost:8000/api/domain/register \
    -H "Content-Type: application/json" \
    -d '{
        "domain": "example.com",
        "period": 1,
        "registrant": {
            "firstName": "John",
            "lastName": "Doe",
            "company": "Example Inc.",
            "email": "john@example.com",
            "addressLine1": "123 Main St",
            "city": "New York",
            "state": "NY",
            "country": "US",
            "zipCode": "10001",
            "phone": "+1234567890"
        },
        "admin": {},
        "technical": {},
        "billing": {},
        "nameservers": [
            "ns1.example.com",
            "ns2.example.com"
        ],
        "privacyProtection": true
    }'
```

---

## 💻 Using the Service in Your Code

```php
use Estratos\DomainNameApi\Domain\Contract\DomainProviderInterface;
use Estratos\DomainNameApi\Domain\DTO\RegisterDomainRequest;
use Estratos\DomainNameApi\Domain\DTO\ContactDto;

class MyService
{
    public function __construct(
        private readonly DomainProviderInterface $domainProvider
    ) {
    }

    public function checkDomain(string $domain): void
    {
        $result = $this->domainProvider->check($domain);

        if ($result->isAvailable()) {
            echo "Domain {$result->domain} is available for {$result->getFormattedPrice()}";
        }
    }

    public function registerDomain(string $domain): void
    {
        $contact = new ContactDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
        );

        $request = new RegisterDomainRequest(
            domain: $domain,
            period: 1,
            registrant: $contact,
            admin: $contact,
            technical: $contact,
            billing: $contact,
            nameservers: [
                'ns1.example.com',
                'ns2.example.com'
            ]
        );

        $result = $this->domainProvider->register($request);

        if ($result->isSuccess()) {
            echo "Domain registered successfully! Order ID: {$result->orderId}";
        }
    }
}
```

---

## 🏗️ Architecture

The bundle follows a Clean Architecture approach with Ports & Adapters.

```text
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                        │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐      │
│  │ Use Cases   │  │   Services  │  │    Commands     │      │
│  └─────────────┘  └─────────────┘  └─────────────────┘      │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      Domain Layer                           │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐      │
│  │ Contracts   │  │    DTOs     │  │ Value Objects   │      │
│  └─────────────┘  └─────────────┘  └─────────────────┘      │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                  Infrastructure Layer                       │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐      │
│  │ REST Client │  │ SOAP Client │  │ Controllers     │      │
│  └─────────────┘  └─────────────┘  └─────────────────┘      │
└─────────────────────────────────────────────────────────────┘
```

### Key Interfaces

#### DomainProviderInterface

Main interface for domain operations.

#### RestClientInterface

HTTP client abstraction for REST API communication.

#### ProviderFactoryInterface

Factory responsible for creating provider implementations.

---

## 🧪 Testing

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run PHPStan analysis
composer phpstan

# Run CS Fixer
composer cs-fix
```

---

## 🔧 Configuration Reference

| Parameter | Type | Default | Description |
|------------|---------|---------|-------------|
| provider | string | rest | Provider to use (rest or soap) |
| api_key | string | `%env(DOMAINNAME_API_KEY)%` | REST API Key |
| reseller_id | string | `%env(DOMAINNAME_RESELLER_ID)%` | Reseller ID |
| rest.endpoint | string | `%env(DOMAINNAME_API_ENDPOINT)%` | REST API endpoint |
| rest.verify_ssl | boolean | true | Verify SSL certificate |
| rest.timeout | integer | 30 | Request timeout in seconds |
| rest.retry_attempts | integer | 3 | Number of retry attempts |
| username | string | `%env(DOMAINNAME_API_USERNAME)%` | SOAP username |
| password | string | `%env(DOMAINNAME_API_PASSWORD)%` | SOAP password |
| test_mode | boolean | false | SOAP test mode |
| timeout | integer | 30 | General timeout |
| default_nameservers | array | `['ns1.domainnameapi.com', 'ns2.domainnameapi.com']` | Default nameservers |

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch

```bash
git checkout -b feature/amazing-feature
```

3. Commit your changes

```bash
git commit -m "Add some amazing feature"
```

4. Push to the branch

```bash
git push origin feature/amazing-feature
```

5. Open a Pull Request

---

## 📝 Changelog

### v0.2.6
- Initial release with SOAP support


### v1.0.0

- Initial release with REST API support
- Domain check functionality
- Domain registration
- Account balance retrieval
- Domain listing
- TLD listing
- Symfony commands
- Symfony controllers
- Clean Architecture implementation
- SOAP provider compatibility layer

### Planned for v1.1

- Domain Renewal
- Domain Transfer
- WHOIS Information
- DNS Management

---

## 📄 License

This bundle is licensed under the MIT License.

See the `LICENSE` file for more information.

---

## 🔗 Links

- DomainResellerAPI Documentation
- GitHub Repository
- Packagist

---

## 👨‍💻 Author

Julio Rodriguez

GitHub: https://github.com/estratos

---

## 🙏 Support

For support, bug reports, or feature requests, please open an issue on GitHub.