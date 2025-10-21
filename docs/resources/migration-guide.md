---
layout: default
title: Migration Guide
parent: Resources
nav_order: 3
description: Guide for upgrading between versions of Color Palette PHP
keywords: migration, upgrade, breaking-changes, deprecations
---

# Migration Guide

This guide helps you upgrade Color Palette PHP between major versions.

## Version 2.x to 3.x

> **Status:** Future release (planned)

### Breaking Changes

#### 1. Minimum PHP Version

**Before (v2.x):**
```json
{
  "require": {
    "php": ">=7.4"
  }
}
```

**After (v3.x):**
```json
{
  "require": {
    "php": ">=8.1"
  }
}
```

**Migration:** Upgrade to PHP 8.1 or higher before upgrading the package.

#### 2. Constructor Changes

**Before (v2.x):**
```php
$palette = new ColorPalette();
$palette->loadImage('image.jpg');
$colors = $palette->getColors();
```

**After (v3.x):**
```php
// Static factory method preferred
$palette = ColorPalette::fromImage('image.jpg')->extract();
$colors = $palette->getColors();
```

**Migration:** Replace direct instantiation with static factory methods.

#### 3. Type Declarations

**Before (v2.x):**
```php
public function getColors(): array
{
    return $this->colors;
}
```

**After (v3.x):**
```php
public function getColors(): Collection
{
    return $this->colors;
}
```

**Migration:** Update code expecting arrays to work with Collection objects:

```php
// Old way
foreach ($palette->getColors() as $color) {
    // ...
}

// New way (same syntax, but Collection has extra methods)
$palette->getColors()
    ->filter(fn($c) => $c->isDark())
    ->map(fn($c) => $c->getHex())
    ->toArray();
```

### Deprecated Features

#### 1. Legacy Methods

**Deprecated:**
```php
$palette->setAlgorithm('kmeans');  // Deprecated
```

**Replacement:**
```php
use Farzai\ColorPalette\Extractors\KMeansExtractor;

$palette->setExtractor(new KMeansExtractor());
```

#### 2. Configuration Arrays

**Deprecated:**
```php
$palette->setOptions([
    'quality' => 5,
    'colors' => 10
]);
```

**Replacement:**
```php
$palette->setQuality(5)
    ->setColorCount(10);
```

### New Features

#### 1. Fluent Interface

```php
$palette = ColorPalette::fromImage('image.jpg')
    ->setColorCount(10)
    ->setQuality(8)
    ->setMaxDimension(1000)
    ->extract();
```

#### 2. Color Collections

```php
$colors = $palette->getColors()
    ->filter(fn($c) => $c->getLuminance() > 0.5)
    ->sortByHue()
    ->take(5);
```

#### 3. Advanced Filtering

```php
$vibrantColors = $palette->filterBySaturation(min: 0.7);
$darkColors = $palette->filterByLuminance(max: 0.3);
```

## Version 1.x to 2.x

### Breaking Changes

#### 1. Namespace Changes

**Before (v1.x):**
```php
use Farzai\ColorPalette;
```

**After (v2.x):**
```php
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Color;
```

**Migration:**
```bash
# Update imports across project
find . -name "*.php" -exec sed -i 's/use Farzai\\ColorPalette;/use Farzai\\ColorPalette\\ColorPalette;/g' {} +
```

#### 2. Method Signature Changes

**Before (v1.x):**
```php
$colors = $palette->extract($imagePath, 5);
```

**After (v2.x):**
```php
$palette = ColorPalette::fromImage($imagePath)
    ->setColorCount(5)
    ->extract();
$colors = $palette->getColors();
```

**Migration:** Replace direct extract calls with builder pattern.

#### 3. Return Type Changes

**Before (v1.x):**
```php
// Returns array of hex strings
$colors = $palette->getColors();
// ['#ff0000', '#00ff00', '#0000ff']
```

**After (v2.x):**
```php
// Returns array of Color objects
$colors = $palette->getColors();
// [Color, Color, Color]

// To get hex strings:
$hexColors = array_map(fn($c) => $c->getHex(), $colors);
```

**Migration:**
```php
// Before
foreach ($colors as $hex) {
    echo $hex;
}

// After
foreach ($colors as $color) {
    echo $color->getHex();
}
```

#### 4. Configuration Changes

**Before (v1.x):**
```php
ColorPalette::$defaultQuality = 5;
ColorPalette::$defaultColorCount = 10;
```

**After (v2.x):**
```php
// Configuration per instance
$palette = ColorPalette::fromImage('image.jpg')
    ->setQuality(5)
    ->setColorCount(10);

// Or set defaults via config file
```

### Deprecated Features

#### 1. Static Configuration

**Deprecated:**
```php
ColorPalette::setDefaultQuality(5);
```

**Replacement:**
```php
// Use instance methods or configuration
$palette->setQuality(5);
```

#### 2. Global Functions

**Deprecated:**
```php
$colors = extract_colors('image.jpg');
```

**Replacement:**
```php
$colors = ColorPalette::fromImage('image.jpg')
    ->extract()
    ->getColors();
```

### New Features in 2.x

#### 1. Color Object API

```php
$color = Color::fromHex('#3498db');

// Rich API
$color->toRgb();           // [52, 152, 219]
$color->toHsl();           // [204, 70, 53]
$color->getLuminance();    // 0.53
$color->isDark();          // false
$color->getComplementary(); // Color object
```

#### 2. Advanced Extraction Options

```php
$palette = ColorPalette::fromImage('image.jpg')
    ->setRegion(0, 0, 200, 200)  // Extract from region
    ->setMaxDimension(1000)       // Limit size
    ->setQuality(8)               // Higher quality
    ->extract();
```

#### 3. Color Filtering and Sorting

```php
$darkColors = $palette->filterDark();
$palette->sortByLuminance();
$vibrant = $palette->filterBySaturation(min: 0.6);
```

#### 4. Performance Improvements

- 40% faster extraction
- 30% lower memory usage
- Better handling of large images

### Migration Steps

1. **Update Composer:**
   ```bash
   composer require farzai/color-palette-php:^2.0
   ```

2. **Update Imports:**
   ```php
   // Old
   use Farzai\ColorPalette;

   // New
   use Farzai\ColorPalette\ColorPalette;
   use Farzai\ColorPalette\Color;
   ```

3. **Update Method Calls:**
   ```php
   // Old
   $colors = ColorPalette::extract($path, 5);

   // New
   $colors = ColorPalette::fromImage($path)
       ->setColorCount(5)
       ->extract()
       ->getColors();
   ```

4. **Update Color Handling:**
   ```php
   // Old
   foreach ($colors as $hex) {
       echo $hex;
   }

   // New
   foreach ($colors as $color) {
       echo $color->getHex();
       // Access rich Color API
       if ($color->isDark()) {
           // ...
       }
   }
   ```

5. **Run Tests:**
   ```bash
   vendor/bin/phpunit
   ```

## Version 0.x to 1.x

### Breaking Changes

#### 1. Package Rename

**Before (v0.x):**
```json
{
  "require": {
    "farzai/php-color-palette": "^0.5"
  }
}
```

**After (v1.x):**
```json
{
  "require": {
    "farzai/color-palette-php": "^1.0"
  }
}
```

**Migration:**
```bash
composer remove farzai/php-color-palette
composer require farzai/color-palette-php:^1.0
```

#### 2. Class Renames

**Before (v0.x):**
```php
use Farzai\PhpColorPalette\Extractor;
```

**After (v1.x):**
```php
use Farzai\ColorPalette;
```

#### 3. Method Changes

**Before (v0.x):**
```php
$extractor = new Extractor();
$colors = $extractor->getColorsFromImage($path);
```

**After (v1.x):**
```php
$colors = ColorPalette::extract($path);
```

### New Features in 1.x

- Simplified API
- Better error handling
- Improved documentation
- Unit tests
- CI/CD pipeline

## Upgrade Checklist

### For Any Version Upgrade

- [ ] Read changelog and breaking changes
- [ ] Update composer.json version constraint
- [ ] Run `composer update`
- [ ] Update imports and namespaces
- [ ] Update method calls
- [ ] Run tests: `vendor/bin/phpunit`
- [ ] Test in development environment
- [ ] Update documentation
- [ ] Deploy to staging
- [ ] Monitor for issues

### Testing After Migration

```php
// Test basic extraction
$palette = ColorPalette::fromImage('test.jpg')->extract();
assert(count($palette->getColors()) > 0);

// Test color operations
$color = $palette->getColors()[0];
assert($color instanceof Color);
assert(preg_match('/^#[0-9A-F]{6}$/', $color->getHex()));

// Test advanced features
$dark = $palette->filterDark();
$palette->sortByLuminance();
```

## Deprecation Timeline

### Current Version (2.x)

| Feature | Status | Removal Version | Alternative |
|---------|--------|----------------|-------------|
| Static configuration | Deprecated | 3.0 | Instance methods |
| Array return types | Deprecated | 3.0 | Collection objects |
| Legacy extractors | Deprecated | 3.0 | New extractor interface |
| Global functions | Removed | - | Class methods |

### Planned (3.x)

| Feature | Status | Removal Version | Alternative |
|---------|--------|----------------|-------------|
| PHP 7.4 support | To be removed | 3.0 | PHP 8.1+ |
| Array-based config | To be removed | 3.0 | Fluent interface |
| Old namespace | To be removed | 3.0 | New namespace structure |

## Getting Help

### Resources

- [Changelog](changelog.md) - Full version history
- [FAQ](faq.md) - Common questions
- [Troubleshooting](troubleshooting.md) - Problem solving
- [Examples](../examples/) - Code samples

### Support Channels

1. **GitHub Issues:** Report bugs or migration issues
2. **Discussions:** Ask questions about migration
3. **Documentation:** Check API docs for new features

### Migration Support

If you need help migrating:

1. Check [migration examples](../examples/migration/)
2. Review [breaking changes in changelog](changelog.md)
3. Open a discussion on GitHub
4. Tag issues with `migration` label

## See Also

- [Changelog](changelog.md)
- [FAQ](faq.md)
- [Contributing Guidelines](contributing.md)
- [API Documentation](../api/)
