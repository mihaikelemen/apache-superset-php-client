# Usage Guide

This guide provides some examples for using the Apache Superset PHP Client library.

## Table of Contents

- [Authentication](#authentication)
- [Dashboard Operations](#dashboard-operations)
- [Guest Token Management](#guest-token-management)
- [CSRF Token Handling](#csrf-token-handling)
- [Direct API Calls](#direct-api-calls)
- [Advanced Configuration](#advanced-configuration)

## Authentication

The library supports multiple authentication ways to interact with Apache Superset.

### Authenticated Client Creation

Create a client with immediate authentication:

```php
<?php

use Superset\SupersetFactory;

$supersetClient = SupersetFactory::createAuthenticated(
    baseUrl: 'https://your-superset-instance.com',
    username: 'your-username',
    password: 'your-password'
);
```

### Manual Authentication

Initialize the client first, then authenticate separately:

```php
<?php

use Superset\SupersetFactory;

$supersetClient = SupersetFactory::create('https://your-superset-instance.com');
$supersetClient->auth()->authenticate('username', 'password');
```

### Bearer Token Authentication

Use an existing access token for authentication:

```php
<?php

use Superset\SupersetFactory;

$supersetClient = SupersetFactory::create('https://your-superset-instance.com');
$supersetClient->auth()->setAccessToken('your-bearer-token');
```

## Dashboard Operations

### Retrieving a Single Dashboard

Dashboards can be retrieved by ID or slug:

```php
<?php

// Retrieve by numeric ID
$dashboard = $supersetClient->dashboard()->get('123');

// Retrieve by slug identifier
$dashboard = $supersetClient->dashboard()->get('sales-dashboard');

// Access dashboard properties
echo $dashboard->title;
echo $dashboard->url;
echo $dashboard->isPublished ? 'Published' : 'Draft';
```

### Listing Dashboards

Retrieve multiple dashboards with optional filtering:

```php
<?php

// Retrieve all dashboards
$dashboards = $supersetClient->dashboard()->list();

// Filter dashboards by tag
$salesDashboards = $supersetClient->dashboard()->list(tag: 'sales');

// Retrieve only published dashboards
$publishedDashboards = $supersetClient->dashboard()->list(onlyPublished: true);

// Combine filters
$taggedPublished = $supersetClient->dashboard()->list(
    tag: 'sales',
    onlyPublished: true
);
```

### Getting Dashboard Embedded UUID

Retrieve the UUID required for embedding a dashboard in an iframe:

```php
<?php

$uuid = $supersetClient->dashboard()->uuid('my-dashboard');
```

## Guest Token Management

Generate guest tokens for embedded dashboard access without requiring user authentication:

```php
<?php

$guestToken = $supersetClient->auth()->createGuestToken(
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

## CSRF Token Handling

The library handles CSRF token management for protected operations:

```php
<?php

// Request a CSRF token
$csrfToken = $supersetClient->auth()->requestCsrfToken();

// The token is automatically included in subsequent requests
$result = $supersetClient->post('some/endpoint', ['data' => 'value']);
```

## Direct API Calls

The client provides methods for all standard HTTP operations.

### GET Request

```php
<?php

$result = $supersetClient->get('chart', ['q' => 'some-filter']);
```

### POST Request

```php
<?php

$result = $supersetClient->post('dataset', [
    'database' => 1,
    'table_name' => 'my_table',
]);
```

### PUT Request

```php
<?php

$result = $supersetClient->put('dashboard/123', [
    'dashboard_title' => 'Updated Title',
]);
```

### DELETE Request

```php
<?php

$result = $supersetClient->delete('dashboard/123');
```

## Advanced Configuration

### Custom HTTP Client Configuration

Configure the HTTP client with specific settings:

```php
<?php

use Superset\Config\HttpClientConfig;
use Superset\SupersetFactory;

$httpConfig = new HttpClientConfig(
    baseUrl: 'https://your-superset-instance.com',
    timeout: 60.0,
    verifySsl: true,
    maxRedirects: 5,
    userAgent: 'MyApp/1.0'
);

$supersetClientClient = SupersetFactory::createWithHttpClientConfig($httpConfig);
```

### Custom Headers

Add default headers that apply to all requests:

```php
<?php

use Superset\Http\HttpClient;

$httpClient = new HttpClient($httpConfig);
$httpClient->addDefaultHeader('X-Custom-Header', 'value');
```

### Logging

The library supports comprehensive logging for debugging and monitoring purposes.

#### Using Monolog

```php
<?php

use Superset\Config\LoggerConfig;
use Superset\Service\LoggerService;
use Superset\SupersetFactory;

$loggerConfig = new LoggerConfig(
    logPath: '/path/to/application.log'
);

$supersetClientClient = SupersetFactory::create(
    baseUrl: 'https://your-superset-instance.com',
    logger: (new LoggerService($loggerConfig))->get()
);
```

#### HTTP Debug Logging

Enable Guzzle's debug logging to inspect raw HTTP traffic:

```php
<?php

use Superset\Config\HttpClientConfig;
use Superset\Config\LoggerConfig;
use Superset\Service\LoggerService;
use Superset\SupersetFactory;

$httpConfig = new HttpClientConfig(
    baseUrl: 'https://your-superset-instance.com',
    debug: fopen('/path/to/guzzle.log', 'a');
);

$loggerConfig = new LoggerConfig(
    logPath: '/path/to/application.log'
);

$supersetClientClient = SupersetFactory::createWithHttpClientConfig(
    httpConfig: $httpConfig,
    logger: (new LoggerService($loggerConfig))->get()
);
```

This configuration enables detailed logging of all HTTP requests and responses, useful for debugging authentication issues or API errors.
