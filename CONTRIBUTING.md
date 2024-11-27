# Contributing to Color Palette PHP

First off, thank you for considering contributing to Color Palette PHP! It's people like you that make Color Palette PHP such a great tool.

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

This section guides you through submitting a bug report for Color Palette PHP. Following these guidelines helps maintainers and the community understand your report, reproduce the behavior, and find related reports.

**Before Submitting A Bug Report**
- Check the documentation for a list of common questions and problems.
- Ensure the bug is not already reported by searching on GitHub under [Issues](https://github.com/farzai/color-palette/issues).
- If you're unable to find an open issue addressing the problem, open a new one.

**How Do I Submit A (Good) Bug Report?**
Bugs are tracked as GitHub issues. Create an issue and provide the following information:

- Use a clear and descriptive title
- Describe the exact steps which reproduce the problem
- Provide specific examples to demonstrate the steps
- Describe the behavior you observed after following the steps
- Explain which behavior you expected to see instead and why
- Include PHP version and relevant environment details
- Include any relevant code snippets or error messages

### Suggesting Enhancements

This section guides you through submitting an enhancement suggestion for Color Palette PHP.

**Before Submitting An Enhancement Suggestion**
- Check if the enhancement has already been suggested.
- Determine which repository the enhancement should be suggested in.
- Perform a cursory search to see if the enhancement has already been suggested.

**How Do I Submit A (Good) Enhancement Suggestion?**
Enhancement suggestions are tracked as GitHub issues. Create an issue and provide the following information:

- Use a clear and descriptive title
- Provide a detailed description of the suggested enhancement
- Provide specific examples to demonstrate the steps
- Describe the current behavior and explain the behavior you expected to see instead
- Explain why this enhancement would be useful to most Color Palette PHP users

### Pull Requests

**Process**

1. Fork the repository
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run the tests (`composer test`)
5. Commit your changes (`git commit -m 'Add some amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

**Development Setup**

1. Clone your fork of the repository
```bash
git clone https://github.com/<your-username>/color-palette.git
```

2. Install dependencies
```bash
composer install
```

3. Run tests
```bash
composer test
```

### Coding Standards

- Follow PSR-12 coding standards
- Use PHP 8.1+ features appropriately
- Add PHPDoc blocks for all classes and methods
- Write tests for new features
- Maintain high test coverage
- Use strict typing (`declare(strict_types=1)`)

### Testing

- Write tests for any new code you create
- Update tests if you modify existing code
- Ensure all tests pass before submitting a pull request
- Aim for high test coverage

**Running Tests**
```bash
# Run all tests
composer test

# Run specific test
./vendor/bin/pest tests/path/to/test.php

# Run with coverage report
composer test-coverage
```

### Documentation

- Update documentation for any changes you make
- Document all public methods and properties
- Include examples in documentation
- Keep README.md updated
- Add PHPDoc blocks to all classes and methods

### Git Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

### PHP Version Support

- Code must be compatible with PHP 8.1 and above
- Use type declarations where possible
- Leverage modern PHP features when appropriate

### Security

- Report security vulnerabilities privately
- Follow secure coding practices
- Never commit sensitive information
- Use proper input validation and sanitization

## Style Guide

### PHP Code Style

```php
declare(strict_types=1);

namespace Farzai\ColorPalette;

use Exception;

/**
 * Class description.
 */
final class Example
{
    private string $property;

    /**
     * Method description.
     *
     * @param string $param Description
     * @return string Description
     * @throws Exception When something goes wrong
     */
    public function method(string $param): string
    {
        // Method implementation
    }
}
```

### Documentation Style

```php
/**
 * Short description.
 *
 * Longer description if needed. Can span
 * multiple lines.
 *
 * @param string $param Description of parameter
 * @return string Description of return value
 * @throws Exception Description of when this is thrown
 */
```

## Additional Notes

### Issue and Pull Request Labels

- `bug`: Something isn't working
- `enhancement`: New feature or request
- `documentation`: Documentation only changes
- `duplicate`: This issue or pull request already exists
- `good first issue`: Good for newcomers
- `help wanted`: Extra attention is needed
- `invalid`: This doesn't seem right
- `question`: Further information is requested
- `wontfix`: This will not be worked on

## Recognition

Contributors are recognized in the following ways:
- Listed in [CONTRIBUTORS.md](CONTRIBUTORS.md)
- Mentioned in release notes
- Added to the GitHub contributors list

## Questions?

Don't hesitate to ask questions about contributing. You can:
- Open an issue with your question
- Contact the maintainers
- Join the community discussions

Thank you for contributing to Color Palette PHP! 