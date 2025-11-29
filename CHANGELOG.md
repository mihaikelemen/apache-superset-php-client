# Changelog

All notable changes to this project will be documented in this file.

## v1.1.3

### Changed
- Improved README.md description with clearer library purpose
- Clarified guest token and CSRF handling explanations in USAGE.md
- Corrected code examples in USAGE.md (dashboard ID type, variable naming consistency)
- `DashboardService::get()` and `DashboardService::uuid()` now accept both `int` and `string` identifiers

### Fixed
- Fixed typos and wording inconsistencies across documentation files
- Corrected acknowledgments section to better reflect library dependencies
- Refactored test suite to remove redundant tests and improve maintainability

## v1.1.2

### Added
- Usage documentation in `docs/USAGE.md`
- Dashboard embedding example in `docs/example/dashboard.php`

### Changed
- Simplified README.md quick start section, moved examples to usage guide
- Updated repository references from `superset-php-client` to `apache-superset-php-client` across documentation
- Migrated from deprecated `Symfony\Component\Serializer\Annotation\SerializedName` to `Symfony\Component\Serializer\Attribute\SerializedName` in `Dashboard` DTO

### Fixed
- Corrected namespace declaration in `DashboardServiceTest` to match actual directory structure (`Superset\Tests\Unit\Service\Component`)

## v1.1.1

### Changed
- Moved `DashboardService` to `Service/Component/` subdirectory for better organization
- Extracted guest user logic from `GuestUserConfig` into new `GuestUserService` class
- Composer `test:coverage` commands now use coverage formats from `phpunit.dist.xml`

### Fixed
- Codecov path mapping to handle Docker container paths (`/app/src/` → `src/`)
- Updated Codecov action to v4 with proper authentication
- Badge URL format updated to current Codecov standards

## v1.1.0

### Added
- Monolog integration for structured application logging
- `LoggerService` for centralized logger management
- `LoggerConfig` for configurable logging (channel, path, level)
- Optional logger injection via `LoggerInterface` in `HttpClient`, `ResponseHandler`, and `SupersetFactory`
- Automatic exception logging through `AbstractException` base class
- Debug stream support in `HttpClientConfig` for Guzzle HTTP debugging

### Changed
- `HttpClient` accepts optional logger, defaults to file-based logging when not provided
- `ResponseHandler` logs HTTP errors and JSON decode failures
- Factory methods accept optional logger parameter for custom logging implementations
- Exception constructors accept optional logger for error tracking
- Test suite updated for new constructor signatures

### Fixed
- Missing Referer header in HTTP redirects
- Error messages now fallback to `$response['msg']` when primary field is unavailable

## v1.0.7

### Added
- New `DashboardService` class for dedicated dashboard operations
- `Superset::dashboard()` method to access the new `DashboardService`

### Changed
- Refactored dashboard-related methods from `Superset` class into dedicated `DashboardService`
- Dashboard service instance is lazily initialized and cached for better performance

### Deprecated
- `Superset::getDashboard()` - Use `dashboard()->get()` instead
- `Superset::getDashboardUuid()` - Use `dashboard()->uuid()` instead
- `Superset::getDashboards()` - Use `dashboard()->list()` instead

## v1.0.6

### Changed
- **Breaking**: Usernames now preserve case instead of being automatically lowercased
- Improved guest username generation in `GuestUserConfig` to handle whitespace by replacing spaces with underscores
- Username generation now uses actual first/last names instead of hardcoded constants for better flexibility
- Refactored `BaseTestCase::invokePrivateMethod()` to `invokeMethod()` for clarity and consistency

### Fixed
- Guest usernames with spaces are now properly formatted with underscores

## v1.0.5

### Added
- `GuestUserConfig` class for standardized and validated guest user attributes
- Default constants for guest user attributes (Guest User with username `guest_user`)

### Changed
- `AuthenticationService` now uses `GuestUserConfig` for guest token creation
- Improved type safety in authentication flow for guest tokens

### Documentation
- Updated documentation to reflect `GuestUserConfig` usage

## v1.0.4

### Fixed
- Error response handling in `ResponseHandler` now properly handles array messages

## v1.0.3

### Changed
- Added `#[\SensitiveParameter]` attribute to sensitive parameters in authentication methods for improved security

## v1.0.2

### Fixed
- Corrected `SerializedName` for `updatedAt` property to `changed_on_utc` in `Dashboard` DTO

## v1.0.1

### Changed
- **Breaking**: Renamed `SupersetFactory::createWithHttpClient()` to `SupersetFactory::createWithHttpClientConfig()`
  - Method now accepts `HttpClientConfig` instead of `HttpClientInterface` and base URL
  - Simplifies factory usage and improves API consistency
- Refactored test suite to use centralized `BASE_URL` constant in `BaseTestCase`
- Added `buildUrl()` helper method to `BaseTestCase` for consistent URL construction in tests
- Updated all test files to use centralized base URL and helper methods
- Updated documentation (README.md and CONTRIBUTING.md) to reflect factory changes

### Fixed
- Improved test maintainability by eliminating hardcoded URLs across test files

## v1.0.0

### Added
- Initial release of Apache Superset PHP Client
- Authentication support (username/password, bearer token)
- Guest token generation
- CSRF token handling
- Dashboard retrieval (single, multiple, filtered by tag)
- Dashboard embedded UUID retrieval
- Generic HTTP methods (GET, POST, PUT, PATCH, DELETE)

### Features
- `SupersetFactory` for easy client instantiation
- `Dashboard` DTO with full type support
- Flexible HTTP client configuration
- Custom header support
