# Basic Color Extraction

This example demonstrates how to extract colors from an image using the Color Palette library.

## Basic Usage

```php
<?php

require 'vendor/autoload.php';

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Path to your image
$imagePath = __DIR__ . '/example-image.jpg';

// Load image (static method)
$image = ImageFactory::createFromPath($imagePath);

// Create a color extractor (using GD backend)
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');

// Extract colors (returns ColorPalette instance)
$palette = $extractor->extract($image, 5);

// Display the colors
foreach ($palette->getColors() as $color) {
    echo sprintf(
        "Color: %s (RGB: %d, %d, %d)\n",
        $color->toHex(),
        $color->getRed(),
        $color->getGreen(),
        $color->getBlue()
    );
}
```

## Advanced Usage

### Using ImageMagick Backend

```php
// Use ImageMagick instead of GD
$extractor = $extractorFactory->make('imagick');
```

### Error Handling

```php
use Farzai\ColorPalette\Exceptions\ImageLoadException;
use Farzai\ColorPalette\Exceptions\ImageException;

try {
    $image = ImageFactory::createFromPath($imagePath);
    $palette = $extractor->extract($image, 5);
} catch (ImageLoadException $e) {
    echo "Failed to load image: " . $e->getMessage() . "\n";
    exit(1);
} catch (ImageException $e) {
    echo "Error processing image: " . $e->getMessage() . "\n";
    exit(1);
}
```

### Working with the Palette

```php
// Get suggested text color for the first color in palette
$backgroundColor = $palette->getColors()[0];
$textColor = $palette->getSuggestedTextColor($backgroundColor);

echo sprintf(
    "Background Color: %s\nSuggested Text Color: %s\n",
    $backgroundColor->toHex(),
    $textColor->toHex()
);

// Get surface colors for UI elements
$surfaceColors = $palette->getSuggestedSurfaceColors();

echo "Surface Colors:\n";
foreach ($surfaceColors as $type => $color) {
    echo sprintf("%s: %s\n", $type, $color->toHex());
}
```

## Complete Example

Here's a complete example that puts it all together:

```php
<?php

require 'vendor/autoload.php';

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\Exceptions\ImageLoadException;
use Farzai\ColorPalette\Exceptions\ImageException;

function analyzeImage(string $imagePath): void
{
    try {
        // Initialize factory
        $extractorFactory = new ColorExtractorFactory();

        // Load image and create extractor
        $image = ImageFactory::createFromPath($imagePath);
        $extractor = $extractorFactory->make('gd');

        // Extract colors (returns ColorPalette instance)
        $palette = $extractor->extract($image, 5);
        
        // Display extracted colors
        echo "Extracted Colors:\n";
        foreach ($palette->getColors() as $index => $color) {
            echo sprintf(
                "%d. %s (RGB: %d, %d, %d)\n",
                $index + 1,
                $color->toHex(),
                $color->getRed(),
                $color->getGreen(),
                $color->getBlue()
            );
        }
        
        // Display surface colors
        echo "\nSurface Colors:\n";
        foreach ($palette->getSuggestedSurfaceColors() as $type => $color) {
            echo sprintf("%s: %s\n", $type, $color->toHex());
            
            // Show suggested text color for each surface
            $textColor = $palette->getSuggestedTextColor($color);
            echo sprintf("  Text Color: %s\n", $textColor->toHex());
        }
        
    } catch (ImageLoadException $e) {
        echo "Failed to load image: " . $e->getMessage() . "\n";
        exit(1);
    } catch (ImageException $e) {
        echo "Error processing image: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Run the analysis
analyzeImage(__DIR__ . '/example-image.jpg');
```

## Expected Output

```
Extracted Colors:
1. #2196f3 (RGB: 33, 150, 243)
2. #1976d2 (RGB: 25, 118, 210)
3. #bbdefb (RGB: 187, 222, 251)

Surface Colors:
surface: #bbdefb
  Text Color: #000000
background: #2196f3
  Text Color: #ffffff
accent: #1976d2
  Text Color: #ffffff
surface_variant: #a7d1f9
  Text Color: #000000
``` 