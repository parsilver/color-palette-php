---
layout: default
title: Contributing
parent: Resources
nav_order: 5
description: How to contribute to Color Palette PHP
keywords: contributing, development, guidelines, community
---

# Contributing Guidelines

Thank you for considering contributing to Color Palette PHP! This guide will help you get started.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [How to Contribute](#how-to-contribute)
- [Reporting Issues](#reporting-issues)
- [Submitting Pull Requests](#submitting-pull-requests)
- [Code Style](#code-style)
- [Testing](#testing)
- [Documentation](#documentation)
- [Community](#community)

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive environment for all contributors.

### Our Standards

**Positive behaviors:**
- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what's best for the community
- Showing empathy towards other community members

**Unacceptable behaviors:**
- Harassment of any kind
- Trolling or insulting comments
- Publishing others' private information
- Any conduct which could reasonably be considered inappropriate

### Enforcement

Report unacceptable behavior to the project maintainers. All complaints will be reviewed and investigated promptly and fairly.

## Getting Started

### Prerequisites

- PHP 7.4 or higher (PHP 8.1+ recommended)
- Composer
- Git
- GD extension for PHP
- Basic understanding of color theory (helpful but not required)

### Fork and Clone

1. **Fork the repository** on GitHub
2. **Clone your fork:**
   ```bash
   git clone https://github.com/YOUR-USERNAME/color-palette-php.git
   cd color-palette-php
   ```
3. **Add upstream remote:**
   ```bash
   git remote add upstream https://github.com/farzai/color-palette-php.git
   ```

## Development Setup

### Install Dependencies

```bash
composer install
```

### Verify Setup

```bash
# Run tests
composer test

# Run code style checks
composer lint

# Run static analysis
composer analyze

# Run all checks
composer check
```

### Development Tools

The project uses:
- **PHPUnit** - Testing framework
- **PHP CS Fixer** - Code style fixer
- **PHPStan** - Static analysis
- **Psalm** - Additional static analysis

### Environment Configuration

Create a `.env` file for local configuration (optional):

```env
PHP_VERSION=8.1
MEMORY_LIMIT=256M
```

## How to Contribute

### Types of Contributions

1. **Bug Fixes** - Fix issues and improve stability
2. **Features** - Add new functionality
3. **Documentation** - Improve or add documentation
4. **Tests** - Increase test coverage
5. **Performance** - Optimize existing code
6. **Examples** - Add usage examples

### Before You Start

1. **Check existing issues** - Avoid duplicate work
2. **Discuss major changes** - Open an issue first
3. **Keep PRs focused** - One feature/fix per PR
4. **Follow conventions** - Match existing code style

## Reporting Issues

### Bug Reports

Use the bug report template and include:

```markdown
## Description
Clear description of the bug

## Steps to Reproduce
1. Step one
2. Step two
3. Step three

## Expected Behavior
What should happen

## Actual Behavior
What actually happens

## Environment
- PHP Version: 8.1.0
- Package Version: 2.1.0
- OS: Ubuntu 22.04
- GD Version: 2.3.3

## Code Sample
```php
// Minimal reproducible example
$palette = ColorPalette::fromImage('test.jpg');
// ...
```

## Additional Context
Screenshots, logs, etc.
```

### Feature Requests

Use the feature request template:

```markdown
## Problem
What problem does this solve?

## Proposed Solution
How should it work?

## Alternatives Considered
Other approaches you've thought about

## Example Usage
```php
// How would you use this feature?
$palette->newFeature();
```

## Additional Context
Mockups, references, etc.
```

### Security Issues

**Do not** open public issues for security vulnerabilities.

Email security concerns to: security@farzai.com

## Submitting Pull Requests

### PR Workflow

1. **Create a branch:**
   ```bash
   git checkout -b feature/my-feature
   # or
   git checkout -b fix/issue-123
   ```

2. **Make changes:**
   - Write code
   - Add tests
   - Update documentation

3. **Commit changes:**
   ```bash
   git add .
   git commit -m "Add feature: description"
   ```

4. **Keep branch updated:**
   ```bash
   git fetch upstream
   git rebase upstream/main
   ```

5. **Push to your fork:**
   ```bash
   git push origin feature/my-feature
   ```

6. **Open Pull Request** on GitHub

### PR Guidelines

#### PR Title Format

Use conventional commits format:

```
feat: Add HSV color space support
fix: Resolve memory leak in batch processing
docs: Update installation instructions
test: Add tests for color conversion
perf: Optimize K-means clustering
refactor: Simplify color extraction logic
chore: Update dependencies
```

#### PR Description Template

```markdown
## Description
Brief description of changes

## Related Issue
Fixes #123

## Changes Made
- Change 1
- Change 2
- Change 3

## Testing
- [ ] Unit tests added/updated
- [ ] Manual testing completed
- [ ] All tests passing

## Breaking Changes
None / List breaking changes

## Screenshots
If applicable

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] Tests added/updated
- [ ] All tests passing
- [ ] No new warnings
```

### Review Process

1. **Automated checks** must pass:
   - PHPUnit tests
   - Code style (PHP CS Fixer)
   - Static analysis (PHPStan, Psalm)
   - Coverage requirements (>85%)

2. **Maintainer review:**
   - Code quality
   - Test coverage
   - Documentation
   - API design

3. **Address feedback:**
   - Make requested changes
   - Push updates
   - Request re-review

4. **Merge:**
   - Maintainer will merge when approved
   - Squash commits if needed

## Code Style

### PHP Standards

We follow **PSR-12** coding standard.

#### Basic Rules

```php
<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

/**
 * Class documentation
 */
class Example
{
    /**
     * Method documentation
     */
    public function method(string $param): array
    {
        // Use type hints
        // Use strict types
        // Use return types

        return [];
    }
}
```

#### Naming Conventions

```php
// Classes: PascalCase
class ColorExtractor {}

// Methods: camelCase
public function extractColors() {}

// Properties: camelCase
private string $imagePath;

// Constants: UPPER_SNAKE_CASE
const MAX_COLORS = 256;

// Variables: camelCase
$colorPalette = [];
```

#### Code Formatting

```php
// Indentation: 4 spaces
// Opening braces on same line
// Closing braces on new line

if ($condition) {
    // code
} elseif ($other) {
    // code
} else {
    // code
}

// Array formatting
$array = [
    'key1' => 'value1',
    'key2' => 'value2',
];

// Method chains
$result = $object
    ->method1()
    ->method2()
    ->method3();
```

### Run Code Style Fixer

```bash
# Check style
composer lint

# Fix style issues
composer lint:fix
```

### Static Analysis

```bash
# Run PHPStan
composer analyze

# Run Psalm
composer psalm
```

## Testing

### Writing Tests

#### Test Structure

```php
<?php

namespace Farzai\ColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use Farzai\ColorPalette\ColorPalette;

class ColorPaletteTest extends TestCase
{
    public function test_it_extracts_colors_from_image(): void
    {
        // Arrange
        $imagePath = __DIR__ . '/fixtures/test.jpg';

        // Act
        $palette = ColorPalette::fromImage($imagePath)->extract();

        // Assert
        $this->assertInstanceOf(ColorPalette::class, $palette);
        $this->assertCount(5, $palette->getColors());
    }
}
```

#### Test Guidelines

1. **One assertion per test** (when possible)
2. **Use descriptive names** - `test_it_does_something`
3. **Follow AAA pattern** - Arrange, Act, Assert
4. **Test edge cases** - Empty, null, invalid inputs
5. **Use fixtures** - Store test images in `tests/fixtures/`
6. **Mock external dependencies** - Don't hit real APIs

#### Test Coverage

Target: **>85% code coverage**

```bash
# Run tests with coverage
composer test:coverage

# Generate HTML report
composer test:coverage:html
```

### Running Tests

```bash
# All tests
composer test

# Specific test
vendor/bin/phpunit tests/ColorPaletteTest.php

# With coverage
composer test:coverage

# Watch mode (if configured)
composer test:watch
```

## Documentation

### Code Documentation

Use PHPDoc for all public APIs:

```php
/**
 * Extract colors from an image
 *
 * @param string $imagePath Path to image file
 * @param int $colorCount Number of colors to extract
 * @return Color[] Array of Color objects
 * @throws InvalidArgumentException If image path is invalid
 * @throws RuntimeException If extraction fails
 */
public function extract(string $imagePath, int $colorCount = 5): array
{
    // Implementation
}
```

### Markdown Documentation

- Use clear headings
- Include code examples
- Add cross-references
- Keep it concise
- Use proper formatting

#### Example Format

```markdown
## Feature Name

Description of the feature.

### Usage

```php
// Code example
$result = $object->method();
```

### Parameters

- `param1` (string) - Description
- `param2` (int) - Description

### Returns

Description of return value.

### Example

Full working example.
```

### Documentation Types

1. **README.md** - Overview and quick start
2. **API docs** - Method documentation
3. **Guides** - How-to articles
4. **Examples** - Code samples
5. **Changelog** - Version history

## Community

### Communication Channels

- **GitHub Issues** - Bug reports and features
- **GitHub Discussions** - Questions and ideas
- **Pull Requests** - Code contributions

### Getting Help

1. Check [FAQ](faq.md)
2. Search [existing issues](https://github.com/farzai/color-palette-php/issues)
3. Ask in [Discussions](https://github.com/farzai/color-palette-php/discussions)
4. Stack Overflow tag: `color-palette-php`

### Recognition

Contributors are recognized in:
- README.md contributors section
- Release notes
- GitHub contributors page

## Development Tips

### Debugging

```php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Use var_dump for debugging
var_dump($variable);

// Use PHPUnit debugging
$this->expectException(Exception::class);
```

### Performance Testing

```php
// Benchmark execution
$start = microtime(true);

// Code to benchmark
$palette->extract();

$duration = microtime(true) - $start;
echo "Execution time: {$duration}s\n";
```

### Common Issues

1. **Tests failing locally:**
   - Check PHP version
   - Verify GD extension
   - Clear composer cache: `composer clear-cache`

2. **Style checks failing:**
   - Run fixer: `composer lint:fix`
   - Check PHPStan: `composer analyze`

3. **Memory issues:**
   - Increase limit: `php -d memory_limit=512M vendor/bin/phpunit`

## Release Process

For maintainers:

1. Update version in relevant files
2. Update CHANGELOG.md
3. Create git tag: `git tag v2.1.0`
4. Push tag: `git push origin v2.1.0`
5. Create GitHub release
6. Packagist auto-updates

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Questions?

- Open a [Discussion](https://github.com/farzai/color-palette-php/discussions)
- Ask in an [Issue](https://github.com/farzai/color-palette-php/issues)
- Email: support@farzai.com

## Thank You!

Your contributions make this project better for everyone. We appreciate your time and effort!

## See Also

- [FAQ](faq.md)
- [Troubleshooting Guide](troubleshooting.md)
- [Migration Guide](migration-guide.md)
- [Changelog](changelog.md)

---

*Last updated: 2024-01-15*
