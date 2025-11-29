# Apache Superset PHP Client

[![PHP Version](https://img.shields.io/packagist/php-v/mihaikelemen/apache-superset-php-client)](https://packagist.org/packages/mihaikelemen/apache-superset-php-client)
[![Latest Version](https://img.shields.io/packagist/v/mihaikelemen/apache-superset-php-client)](https://packagist.org/packages/mihaikelemen/apache-superset-php-client)
[![CI](https://github.com/mihaikelemen/apache-superset-php-client/workflows/CI/badge.svg)](https://github.com/mihaikelemen/apache-superset-php-client/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/mihaikelemen/apache-superset-php-client/graph/badge.svg?token=XBBQEU4VUR)](https://codecov.io/gh/mihaikelemen/apache-superset-php-client)
[![License](https://img.shields.io/packagist/l/mihaikelemen/apache-superset-php-client)](https://github.com/mihaikelemen/apache-superset-php-client/blob/main/LICENSE)

A PHP client library for interacting with the [Apache Superset API](https://superset.apache.org/docs/api/). This library provides an easy-to-use interface for authenticating, retrieving dashboards, and managing embedded content.

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

```php
<?php

require 'vendor/autoload.php';

use Superset\SupersetFactory;

// Create an authenticated client
$superset = SupersetFactory::createAuthenticated(
    baseUrl: 'https://your-superset-instance.com',
    username: 'your-username',
    password: 'your-password'
);

// Retrieve dashboards
$dashboards = $superset->dashboard()->list();

// Get a specific dashboard
$dashboard = $superset->dashboard()->get('my-dashboard-slug');
```

## Documentation

For detailed usage instructions, authentication methods, and advanced configuration options, please refer to the [USAGE.md](docs/USAGE.md).

## Features

- Multiple authentication methods (username/password, bearer token)
- Dashboard operations (list, retrieve, embed)
- Guest token generation for embedded dashboards
- CSRF token management
- Direct API access for all HTTP methods
- Configurable HTTP client settings
- Logging support for debugging and monitoring

## Contributing

Contributions are welcome! Please refer to the [CONTRIBUTING.md](CONTRIBUTING.md) file for guidelines.

## License

This library is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Uses [GuzzleHTTP](https://github.com/guzzle/guzzle) as the HTTP client
- Uses [Symfony Serializer](https://symfony.com/doc/current/components/serializer.html) for data transformation
- Uses [Monolog](https://github.com/Seldaek/monolog) for logging
