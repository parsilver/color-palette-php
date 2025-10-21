---
layout: default
title: Changelog
parent: Resources
nav_order: 4
description: Version history and release notes for Color Palette PHP
keywords: changelog, releases, versions, history
---

# Changelog

All notable changes to Color Palette PHP are documented here.

For the complete changelog, see [GitHub Releases](https://github.com/farzai/color-palette-php/releases).

## Recent Releases

### [2.1.0] - 2024-01-15

#### Added
- Region-based color extraction
- Color similarity filtering
- HSV and CMYK color space support
- Performance benchmarking tools
- Docker development environment

#### Changed
- Improved K-means clustering algorithm (25% faster)
- Enhanced color quantization accuracy
- Better memory management for large images
- Updated documentation with more examples

#### Fixed
- Memory leak in batch processing
- Color conversion precision issues
- PNG transparency handling edge cases
- Race condition in concurrent extraction

#### Performance
- 25% faster color extraction
- 15% lower memory usage
- Better handling of images > 10MB

### [2.0.0] - 2023-11-20

**Major Release** - See [Migration Guide](migration-guide.md#version-1x-to-2x) for upgrade instructions.

#### Breaking Changes
- New namespace structure: `Farzai\ColorPalette\*`
- Fluent builder API replaces direct instantiation
- Color objects replace hex string returns
- Minimum PHP version: 7.4

#### Added
- Rich Color object API with conversion methods
- Fluent interface for palette extraction
- Advanced filtering and sorting options
- Color scheme generation
- Luminance and contrast ratio calculations
- HSL/HSV color space support
- Comprehensive test suite (90% coverage)

#### Changed
- Complete API redesign for better usability
- Improved color extraction accuracy
- Better error handling and validation
- Enhanced documentation

#### Performance
- 40% faster extraction algorithm
- 30% lower memory consumption
- Optimized image resizing

#### Deprecated
- Static configuration methods
- Global helper functions
- Old namespace (removed in 3.0)

### [1.2.5] - 2023-08-10

#### Fixed
- Critical bug in JPEG image handling
- WebP format support on PHP 7.4
- Color quantization edge cases

#### Security
- Input validation for image paths
- File type verification improvements

### [1.2.0] - 2023-06-15

#### Added
- WebP image format support
- Quality setting for extraction
- Maximum dimension limiting
- Basic color filtering

#### Changed
- Improved GD extension detection
- Better error messages
- Updated dependencies

#### Fixed
- PNG transparency issues
- Color accuracy improvements
- Memory handling for large images

### [1.1.0] - 2023-03-20

#### Added
- Support for PHP 8.2
- BMP image format support
- Color count configuration
- Basic documentation

#### Changed
- Refactored extraction algorithm
- Improved test coverage (84%)
- Updated examples

#### Fixed
- Composer autoload issues
- Color sorting accuracy
- Edge case handling

### [1.0.0] - 2023-01-10

**Initial Stable Release**

#### Added
- Core color extraction functionality
- Support for JPEG, PNG, GIF
- Basic API
- PHPUnit tests
- README documentation
- MIT License

#### Features
- K-means clustering algorithm
- Configurable color count
- RGB color output
- GD extension support

## Version Highlights

### Major Versions

#### v2.x (Current)
- Modern fluent API
- Rich Color objects
- Advanced filtering
- High performance
- Comprehensive tests

#### v1.x (Legacy)
- Basic extraction
- Simple API
- Array-based output
- Foundation features

#### v0.x (Beta)
- Experimental release
- Core functionality
- Limited features
- Proof of concept

## Breaking Changes by Version

### 2.0.0
- Namespace change: `Farzai\ColorPalette` → `Farzai\ColorPalette\ColorPalette`
- API redesign: Static methods → Fluent builder
- Return types: `array` → `Color[]`
- PHP requirement: 7.2 → 7.4

### 1.0.0
- Package rename: `farzai/php-color-palette` → `farzai/color-palette-php`
- Class rename: `Extractor` → `ColorPalette`
- Method rename: `getColorsFromImage()` → `extract()`

## Deprecation Notices

### Active Deprecations (2.x)

These features are deprecated and will be removed in 3.0:

```php
// ❌ Deprecated in 2.0, removed in 3.0
ColorPalette::setDefaultQuality(5);
ColorPalette::setDefaultColorCount(10);

// ✅ Use instead
$palette = ColorPalette::fromImage('image.jpg')
    ->setQuality(5)
    ->setColorCount(10);
```

```php
// ❌ Deprecated in 2.1, removed in 3.0
$palette->setAlgorithm('kmeans');

// ✅ Use instead
use Farzai\ColorPalette\Extractors\KMeansExtractor;
$palette->setExtractor(new KMeansExtractor());
```

### Removed Features

#### Removed in 2.0
- Global helper functions
- Static configuration
- Old namespace `Farzai\PhpColorPalette`
- Legacy method names

#### Removed in 1.0
- Beta API methods
- Experimental features
- Old package name

## Upgrade Paths

### Upgrading from 1.x to 2.x

```bash
# Update composer.json
composer require farzai/color-palette-php:^2.0

# Update code (see Migration Guide)
```

See [Migration Guide](migration-guide.md#version-1x-to-2x) for detailed instructions.

### Upgrading from 0.x to 1.x

```bash
# Remove old package
composer remove farzai/php-color-palette

# Install new package
composer require farzai/color-palette-php:^1.0
```

## Security Updates

### CVE Notices
No security vulnerabilities have been reported.

### Security Enhancements

#### 2.1.0
- Enhanced input validation
- Improved file type verification
- Path traversal protection

#### 2.0.0
- Secure image loading
- Memory limit enforcement
- Error information sanitization

#### 1.2.5
- File extension validation
- MIME type checking
- Resource cleanup improvements

## Performance Improvements

### Benchmark Comparisons

#### v2.1.0 vs v2.0.0
- Extraction speed: +25%
- Memory usage: -15%
- Large image handling: +40%

#### v2.0.0 vs v1.2.5
- Extraction speed: +40%
- Memory usage: -30%
- API calls: -20%

### Optimization Timeline

| Version | Extraction Time* | Memory Usage* | Image Size Limit |
|---------|-----------------|---------------|------------------|
| 2.1.0   | 0.45s          | 25 MB         | 20 MB           |
| 2.0.0   | 0.60s          | 30 MB         | 15 MB           |
| 1.2.0   | 1.00s          | 43 MB         | 10 MB           |
| 1.0.0   | 1.20s          | 50 MB         | 8 MB            |

*For 2000x2000px image with 10 colors, quality 7

## Roadmap

### Version 3.0 (Planned Q2 2024)

#### Planned Features
- [ ] PHP 8.1 minimum requirement
- [ ] Collection-based return types
- [ ] Advanced color analysis tools
- [ ] Machine learning integration
- [ ] Async processing support
- [ ] GPU acceleration (optional)
- [ ] WebAssembly support

#### Breaking Changes
- Remove deprecated methods
- Update return type declarations
- Modernize API with PHP 8.1 features
- Restructure namespace organization

### Future Considerations
- Neural network color analysis
- Perceptual color grouping
- Accessibility scoring
- Color blindness simulation
- Advanced color harmonies
- Real-time video processing

## Contributing

We welcome contributions! See [Contributing Guidelines](contributing.md) for details.

### How to Propose Changes

1. Open an issue to discuss major changes
2. Fork the repository
3. Create a feature branch
4. Submit a pull request
5. Wait for review

## Versioning

This project follows [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality
- **PATCH** version for backwards-compatible bug fixes

## Links

- **Repository:** https://github.com/farzai/color-palette-php
- **Releases:** https://github.com/farzai/color-palette-php/releases
- **Issues:** https://github.com/farzai/color-palette-php/issues
- **Discussions:** https://github.com/farzai/color-palette-php/discussions
- **Packagist:** https://packagist.org/packages/farzai/color-palette-php

## See Also

- [Migration Guide](migration-guide.md) - Upgrade instructions
- [FAQ](faq.md) - Common questions
- [Troubleshooting](troubleshooting.md) - Problem solving
- [Contributing](contributing.md) - How to contribute

---

*Last updated: 2024-01-15*
