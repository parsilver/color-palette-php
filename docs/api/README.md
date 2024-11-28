# API Documentation

This section provides detailed documentation for all the classes and interfaces in the Color Palette library.

## Core Classes

### Color Handling
- [Color](color.md) - Core color representation and manipulation
- [ColorPalette](color-palette.md) - Collection of colors with analysis features
- [Theme](theme.md) - Structured color themes for applications

### Image Processing
- [ImageFactory](image-loader.md#imagefactory) - Create image instances
- [ImageLoader](image-loader.md) - Load and process images
- [ColorExtractor](color-extractor.md) - Extract colors from images
- [PaletteGenerator](palette-generation.md) - Alternative way to generate palettes

### Factories
- [ColorExtractorFactory](color-extractor.md#colorextractorfactory) - Create color extractors (GD/Imagick)
- [ImageLoaderFactory](image-loader.md#imageloaderfactory) - Create image loaders
- [ImageFactory](image-loader.md#imagefactory) - Create image instances

## Features

### Color Operations
- [Color Manipulation](color-manipulation.md) - Transform and modify colors
- [Color Spaces](color-spaces.md) - RGB, HSL, and Hex conversions
- [Color Schemes](color-schemes.md) - Generate harmonious color combinations

### Utilities
- [Contrast Calculation](utilities.md#contrast) - Calculate color contrast ratios
- [Brightness Analysis](utilities.md#brightness) - Analyze color brightness
- [Surface Colors](utilities.md#surface-colors) - Generate UI surface colors

## Interfaces

The library uses interfaces in the `Contracts` namespace for dependency injection and abstraction:

### Core Interfaces
- `ColorInterface` - Color representation contract
- `ColorPaletteInterface` - Color collection contract
- `ThemeInterface` - Theme generation contract

### Processing Interfaces
- `ColorExtractorInterface` - Color extraction contract
- `ImageInterface` - Image handling contract
- `ImageLoaderInterface` - Image loading contract
- `ThemeGeneratorInterface` - Theme generation contract

## Exception Handling

The library uses the following exceptions:
- `ImageLoadException` - Image loading failures
- `ImageException` - General image processing errors
- `InvalidArgumentException` - Invalid color values or parameters

## Implementation Notes

1. **Color Extraction Methods**
   - Direct extraction using `ColorExtractor`
   - Simplified extraction using `PaletteGenerator`

2. **Image Processing**
   - GD backend (default, faster)
   - ImageMagick backend (more features)

3. **Color Collections**
   - `ColorPalette` implements `ArrayAccess` and `Countable`
   - Supports array-like access to colors
   - Provides color analysis methods