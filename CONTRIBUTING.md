# Contributing to Superset PHP Client

Contributions are welcome and help make this library better for everyone.

## Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone https://github.com/YOUR-USERNAME/apache-superset-php-client.git
   cd apache-superset-php-client
   ```
3. Install dependencies:
   ```bash
   composer install
   ```

## Development Workflow

### Setting Up Your Environment

1. Make sure you have PHP 8.4 or higher installed
2. Install Composer dependencies: `composer install`

### Running Tests

Before submitting a pull request, make sure all tests pass:

```bash
# Run unit tests
composer test

# Run all tests with coverage
composer test:coverage

# Run integration tests (requires Superset instance)
composer test:integration
```

### Code Style

This project follows PSR-12 coding standards with some additional rules enforced by PHP CS Fixer.

```bash
# Check code style
composer cs-check

# Fix code style automatically
composer cs-fix
```

### Static Analysis

Currently, PHPStan is set to level 9.

```bash
composer phpstan
```

### Running All Quality Checks

Before submitting, run all quality checks:

```bash
composer quality
```

## Making Changes

1. Create a new branch for your feature or bugfix:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes following these guidelines:
   - Write clear, descriptive commit messages
   - Follow PSR-12 coding standards
   - Add/update tests for your changes
   - Update documentation if needed
   - Maintain backward compatibility when possible

3. Commit your changes:
   ```bash
   git add .
   git commit -m "Add feature: description of your changes"
   ```

4. Push to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

5. Open a Pull Request on GitHub

## Pull Request Guidelines

- **Title**: Use a clear, descriptive title
- **Description**: Explain what changes you made and why
- **Tests**: Include tests for new features or bug fixes
- **Documentation**: Update the documentation (README.md and CHANGELOG.md) as needed
- **Code Quality**: Ensure all quality checks pass (`composer quality`)
- **One Feature Per PR**: Keep pull requests focused on a single feature or fix
- **Single Commit**: All pull requests must contain exactly ONE commit. Use git rebase to squash multiple commits before submitting

### Squashing Commits

If you have multiple commits in your branch, you must squash them into a single commit before submitting your PR:

```bash
# Interactive rebase to squash commits (assuming you're 3 commits ahead)
git rebase -i HEAD~3

# In the editor, mark all commits except the first as 'squash' or 's'
# Save and close the editor
# Edit the final commit message
# Force push to your fork
git push --force-with-lease origin feature/your-feature-name
```

Alternatively, you can use:

```bash
# Soft reset to keep changes but remove commits
git reset --soft main
git commit -m "feat(scope): your complete change description"
git push --force-with-lease origin feature/your-feature-name
```

## Coding Standards

### PHP

- PHP 8.4+ features are encouraged
- Use strict types: `declare(strict_types=1);`
- Use type hints for all parameters and return types
- Use readonly properties where appropriate
- Follow PSR-12 coding style
- Write self-documenting code with meaningful variable names

### Documentation

- Add PHPDoc blocks for classes and public methods
- Document complex logic with inline comments
- Keep README.md up to date
- Update CHANGELOG.md for notable changes

### Testing

- Write unit tests for all new code
- Aim for high test coverage
- Use descriptive test method names
- Follow the Arrange-Act-Assert pattern
- Mock external dependencies

**Types:**
- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Example:**
```
feat(dashboard): add support for dashboard export

Add new method getDashboardExport() to retrieve dashboard
configuration in JSON format.

Closes #123
```

## Reporting Bugs

When reporting bugs, please include:

1. **Description**: Clear description of the bug
2. **Steps to Reproduce**: Minimal steps to reproduce the issue
3. **Expected Behavior**: What should happen
4. **Actual Behavior**: What actually happens
5. **Environment**:
   - PHP version
   - Library version
   - Superset version
   - Operating system

## Feature Requests

I cannot guarantee that all feature requests will be implemented, but to suggest a feature, please:

1. Check if the feature has already been requested
2. Clearly describe the feature and its use case
3. Explain why it would be useful to the community
4. Be open to discussion and feedback

## Questions?

If you have questions about contributing:

- Open a [GitHub Discussion](https://github.com/mihaikelemen/apache-superset-php-client/discussions)
- Create an [issue](https://github.com/mihaikelemen/apache-superset-php-client/issues) for bugs
- Check existing documentation in README.md

## Code of Conduct

Be respectful and inclusive. We're all here to build something great together.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
