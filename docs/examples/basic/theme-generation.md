# Theme Generation

This example demonstrates how to generate color themes from images using the Color Palette library.

## Basic Usage

```php
<?php

require 'vendor/autoload.php';

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\ThemeGenerator;

// Path to your image
$imagePath = __DIR__ . '/example-image.jpg';

// Create image and extract colors
$imageFactory = new ImageFactory();
$image = $imageFactory->createFromPath($imagePath);

$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->create('gd');

$colors = $extractor->extract($image);
$palette = new ColorPalette($colors);

// Generate theme
$generator = new ThemeGenerator();
$theme = $generator->generate($palette);

// Access theme colors
echo "Theme Colors:\n";
echo sprintf("Primary: %s\n", $theme->getPrimary()->toHex());
echo sprintf("Secondary: %s\n", $theme->getSecondary()->toHex());
echo sprintf("Accent: %s\n", $theme->getAccent()->toHex());
```

## Advanced Usage

### Working with Theme Colors

```php
// Get theme colors
$primary = $theme->getPrimary();
$secondary = $theme->getSecondary();
$accent = $theme->getAccent();

// Check if colors are light or dark
echo sprintf(
    "Primary color is %s\n",
    $primary->isLight() ? 'light' : 'dark'
);

// Get suggested text colors
$textOnPrimary = $palette->getSuggestedTextColor($primary);
$textOnSecondary = $palette->getSuggestedTextColor($secondary);
$textOnAccent = $palette->getSuggestedTextColor($accent);

echo "Text Colors:\n";
echo sprintf("On Primary: %s\n", $textOnPrimary->toHex());
echo sprintf("On Secondary: %s\n", $textOnSecondary->toHex());
echo sprintf("On Accent: %s\n", $textOnAccent->toHex());
```

### Creating a Web Color Scheme

```php
function generateWebColorScheme($theme, $palette): array
{
    $primary = $theme->getPrimary();
    $secondary = $theme->getSecondary();
    $accent = $theme->getAccent();
    
    return [
        'colors' => [
            'primary' => $primary->toHex(),
            'secondary' => $secondary->toHex(),
            'accent' => $accent->toHex(),
            'text' => [
                'onPrimary' => $palette->getSuggestedTextColor($primary)->toHex(),
                'onSecondary' => $palette->getSuggestedTextColor($secondary)->toHex(),
                'onAccent' => $palette->getSuggestedTextColor($accent)->toHex(),
            ],
            'surface' => $palette->getSuggestedSurfaceColors(),
        ],
        'css' => [
            ':root' => [
                '--color-primary' => $primary->toHex(),
                '--color-secondary' => $secondary->toHex(),
                '--color-accent' => $accent->toHex(),
                '--color-text-on-primary' => $palette->getSuggestedTextColor($primary)->toHex(),
                '--color-text-on-secondary' => $palette->getSuggestedTextColor($secondary)->toHex(),
                '--color-text-on-accent' => $palette->getSuggestedTextColor($accent)->toHex(),
            ],
        ],
    ];
}
```

## Complete Example

Here's a complete example that generates a theme and creates a CSS variables file:

```php
<?php

require 'vendor/autoload.php';

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\ThemeGenerator;
use Farzai\ColorPalette\Exceptions\ImageLoadException;
use Farzai\ColorPalette\Exceptions\ImageException;

function generateThemeFromImage(string $imagePath, string $cssOutputPath): void
{
    try {
        // Initialize components
        $imageFactory = new ImageFactory();
        $extractorFactory = new ColorExtractorFactory();
        $image = $imageFactory->createFromPath($imagePath);
        $extractor = $extractorFactory->create('gd');
        
        // Generate palette and theme
        $colors = $extractor->extract($image);
        $palette = new ColorPalette($colors);
        $generator = new ThemeGenerator();
        $theme = $generator->generate($palette);
        
        // Generate color scheme
        $scheme = generateWebColorScheme($theme, $palette);
        
        // Create CSS variables
        $css = ":root {\n";
        foreach ($scheme['css'][':root'] as $variable => $value) {
            $css .= sprintf("    %s: %s;\n", $variable, $value);
        }
        $css .= "}\n";
        
        // Save CSS file
        file_put_contents($cssOutputPath, $css);
        
        // Output theme information
        echo "Theme generated successfully!\n\n";
        echo "Primary Color: " . $theme->getPrimary()->toHex() . "\n";
        echo "Secondary Color: " . $theme->getSecondary()->toHex() . "\n";
        echo "Accent Color: " . $theme->getAccent()->toHex() . "\n";
        echo "\nCSS variables have been saved to: " . $cssOutputPath . "\n";
        
    } catch (ImageLoadException $e) {
        echo "Failed to load image: " . $e->getMessage() . "\n";
        exit(1);
    } catch (ImageException $e) {
        echo "Error processing image: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Generate theme and save CSS
generateThemeFromImage(
    __DIR__ . '/example-image.jpg',
    __DIR__ . '/theme-variables.css'
);
```

## Expected Output

The script will generate a CSS file (`theme-variables.css`) with contents like:

```css
:root {
    --color-primary: #2196f3;
    --color-secondary: #1976d2;
    --color-accent: #bbdefb;
    --color-text-on-primary: #ffffff;
    --color-text-on-secondary: #ffffff;
    --color-text-on-accent: #000000;
}
```

And output to console:

```
Theme generated successfully!

Primary Color: #2196f3
Secondary Color: #1976d2
Accent Color: #bbdefb

CSS variables have been saved to: ./theme-variables.css
``` 