# Apache Superset PHP Client

[![PHP Version](https://img.shields.io/packagist/php-v/mihaikelemen/apache-superset-php-client)](https://packagist.org/packages/mihaikelemen/apache-superset-php-client)
[![Latest Version](https://img.shields.io/packagist/v/mihaikelemen/apache-superset-php-client)](https://packagist.org/packages/mihaikelemen/apache-superset-php-client)
[![CI](https://github.com/mihaikelemen/apache-superset-php-client/workflows/CI/badge.svg)](https://github.com/mihaikelemen/apache-superset-php-client/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/mihaikelemen/apache-superset-php-client/graph/badge.svg?token=XBBQEU4VUR)](https://codecov.io/gh/mihaikelemen/apache-superset-php-client)
[![License](https://img.shields.io/packagist/l/mihaikelemen/apache-superset-php-client)](https://github.com/mihaikelemen/apache-superset-php-client/blob/main/LICENSE)

A PHP client library for interacting with the [Apache Superset API](https://superset.apache.org/docs/api/).

## Installation

Install the library using Composer:

```bash
composer require mihaikelemen/apache-superset-php-client
```

## Requirements

- PHP 8.4 or higher
- ext-curl
- ext-json
- GuzzleHTTP
- Symfony Serializer

## Quick Start

### Basic Usage

```php
<?php

require 'vendor/autoload.php';

use Superset\SupersetFactory;

// Create a client with authentication
$superset = SupersetFactory::createAuthenticated(
    baseUrl: 'https://your-superset-instance.com',
    username: 'your-username',
    password: 'your-password'
);

// Get all dashboards
$dashboards = $superset->dashboard()->list();

```

### Manual Authentication

```php
<?php

use Superset\SupersetFactory;

// Create client without authentication
$superset = SupersetFactory::create('https://your-superset-instance.com');

// Authenticate manually
$superset->auth()->authenticate('username', 'password');

// Now you can make authenticated requests
$dashboard = $superset->dashboard()->get('my-dashboard-slug');
```

### Using Bearer Token

```php
<?php

use Superset\SupersetFactory;

$superset = SupersetFactory::create('https://your-superset-instance.com');

// Set access token directly
$superset->auth()->setAccessToken('your-bearer-token');

// Make requests
$dashboards = $superset->dashboard()->list();
```

## Usage Examples

### Retrieving Dashboards

#### Get a Single Dashboard

```php
<?php

// By ID
$dashboard = $superset->dashboard()->get('123');

// By slug
$dashboard = $superset->dashboard()->get('sales-dashboard');

echo $dashboard->title;
echo $dashboard->url;
echo $dashboard->isPublished ? 'Published' : 'Draft';
```

#### Get All Dashboards

```php
<?php

// Get all dashboards, regardless of their status
$dashboards = $superset->dashboard()->list();

// Get dashboards by tag
$salesDashboards = $superset->dashboard()->list(tag: 'sales');

// Include only published dashboards
$allDashboards = $superset->dashboard()->list(onlyPublished: true);
```

#### Get Dashboard Embedded UUID

```php
<?php

// Get UUID for embedding dashboard in an iframe
$uuid = $superset->dashboard()->uuid('my-dashboard');
```

### Working with Guest Tokens

```php
<?php

// Create a guest token for embedded dashboards
$guestToken = $superset->auth()->createGuestToken(
    userAttributes: [
        'username' => 'guest_user',
        'first_name' => 'Guest',
        'last_name' => 'User',
    ],
    resources: [
        'dashboard' => 'abc-def-123',
    ],
    rls: []
);
```

### CSRF Token Handling

```php
<?php

// Request CSRF token for all operations
$csrfToken = $superset->auth()->requestCsrfToken();

// The token is automatically added to subsequent requests
$result = $superset->post('some/endpoint', ['data' => 'value']);
```

### Direct API Calls

```php
<?php

// GET request
$result = $superset->get('chart', ['q' => 'some-filter']);

// POST request
$result = $superset->post('dataset', [
    'database' => 1,
    'table_name' => 'my_table',
]);

// PUT request
$result = $superset->put('dashboard/123', [
    'dashboard_title' => 'Updated Title',
]);

// PATCH request
$result = $superset->patch('chart/456', [
    'viz_type' => 'bar',
]);

// DELETE request
$result = $superset->delete('dashboard/123');
```

## Advanced Configuration

### Custom HTTP Client

```php
<?php

use Superset\Config\HttpClientConfig;
use Superset\Http\HttpClient;
use Superset\SupersetFactory;

// Create a custom HTTP configuration
$httpConfig = new HttpClientConfig(
    baseUrl: 'https://your-superset-instance.com',
    timeout: 60.0,
    verifySsl: true,
    maxRedirects: 5,
    userAgent: 'MyApp/1.0'
);

// Use with factory
$superset = SupersetFactory::createWithHttpClientConfig($httpConfig);
```

### Custom Headers

```php
<?php

// Create HTTP client with custom headers that apply to all requests
$httpClient = new HttpClient($httpConfig);
$httpClient->addDefaultHeader('X-Custom-Header', 'value');
```

## API Coverage

### Currently Implemented

- Authentication (username/password, bearer token)
- Guest token generation
- CSRF token handling
- Dashboard retrieval (single, multiple, filtered)
- Dashboard embedded UUID
- Generic HTTP methods (GET, POST, PUT, PATCH, DELETE)

### Planned Features

- Chart management
- Dataset operations
- User management
- SQL Lab queries

## Contributing

Contributions are welcome! Please refer to the [CONTRIBUTING.md](CONTRIBUTING.md) file for guidelines.

## License

This library is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Built for [Apache Superset](https://superset.apache.org/)
- Uses [GuzzleHTTP](https://github.com/guzzle/guzzle) for HTTP client
- Uses [Symfony Serializer](https://symfony.com/doc/current/components/serializer.html) for data transformation
