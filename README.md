# DomainName API Bundle for Symfony

A Symfony bundle for integrating with DomainName API domain registration services.

## Installation

### 1. Install via Composer

```bash
composer require estratos/domainname-api
```
Update your .env file:

env
DOMAINNAME_API_USERNAME=your_username_here
DOMAINNAME_API_PASSWORD=your_password_here
DOMAINNAME_API_TEST_MODE=false


3. Verify Installation
Test the connection to the API:

bash
php bin/console domain:balance
Usage
In Controllers
php
use Estratos\DomainNameApi\Service\DomainNameApiClient;

class DomainController extends AbstractController
{
    public function index(DomainNameApiClient $apiClient)
    {
        $domainInfo = $apiClient->GetDetails('example.com');
        $balance = $apiClient->GetCurrentBalance();
        
        // ...
    }
}
Available Commands
domain:check - Check domain availability

domain:balance - Check account balance

domain:list - List domains in account

API Endpoints
The bundle provides optional REST API endpoints:

GET /api/domain/{domain} - Get domain information

GET /api/domain/{domain}/check - Check availability

GET /api/domain/balance - Get account balance

POST /api/domain/register - Register new domain

GET /api/domain/tlds - Get available TLDs

Configuration
See config/packages/domainname_api.yaml for all configuration options.

Support
For issues and questions, please create an issue on GitHub.
