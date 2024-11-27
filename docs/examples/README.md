# Color Palette PHP Examples

This directory contains practical examples of using the Color Palette PHP package in various scenarios.

## Basic Examples

1. [Basic Color Extraction](basic-extraction.md)
   - Extract colors from a local image
   - Extract colors from a URL
   - Handle different image formats

2. [Theme Generation](theme-generation.md)
   - Create themes from images
   - Generate light/dark variants
   - Export themes to CSS

3. [Color Manipulation](color-manipulation.md)
   - Create and modify colors
   - Convert between color formats
   - Calculate color relationships

## Web Implementation Examples

1. [Dynamic Theme Generator](web-implementation/theme-generator.md)
   ```php
   use Farzai\ColorPalette\ColorPaletteFactory;
   use Farzai\ColorPalette\ThemeGenerator;
   
   // Create palette from uploaded image
   $factory = new ColorPaletteFactory();
   $palette = $factory->createFromPath($_FILES['image']['tmp_name']);
   
   // Generate theme
   $generator = new ThemeGenerator();
   $theme = $generator->generate($palette);
   
   // Output CSS variables
   header('Content-Type: text/css');
   echo $theme->toCssVariables('theme-');
   ```

2. [Color Accessibility Checker](web-implementation/accessibility.md)
   ```php
   use Farzai\ColorPalette\Color;
   
   // Check contrast ratio
   $backgroundColor = Color::fromHex('#ffffff');
   $textColor = Color::fromHex('#000000');
   
   $contrast = $textColor->getContrast($backgroundColor);
   $isAccessible = $contrast >= 4.5; // WCAG AA standard
   ```

3. [Image Color Analysis](web-implementation/color-analysis.md)
   ```php
   use Farzai\ColorPalette\ColorPaletteFactory;
   
   // Analyze image colors
   $factory = new ColorPaletteFactory();
   $palette = $factory->createFromPath('image.jpg');
   
   // Get color statistics
   $dominantColor = $palette->getDominantColor();
   $surfaceColors = $palette->getSuggestedSurfaceColors();
   ```

## Advanced Examples

1. [Custom Color Extraction](advanced/custom-extraction.md)
   ```php
   use Farzai\ColorPalette\ColorExtractorFactory;
   use Farzai\ColorPalette\ImageLoader;
   
   // Configure custom extraction
   $loader = new ImageLoader('imagick');
   $image = $loader->load('large-image.jpg');
   
   $factory = new ColorExtractorFactory();
   $extractor = $factory->create('imagick')
       ->setMaxColors(8)
       ->setQuality(90);
   
   $colors = $extractor->extract($image);
   ```

2. [Theme System Integration](advanced/theme-system.md)
   ```php
   use Farzai\ColorPalette\Theme;
   
   // Create base theme
   $lightTheme = Theme::fromColors([
       'primary' => Color::fromHex('#2196f3'),
       'secondary' => Color::fromHex('#f44336'),
       'accent' => Color::fromHex('#4caf50')
   ]);
   
   // Generate dark theme
   $darkTheme = $lightTheme->getDarkVariant();
   
   // Export both themes
   $css = "/* Light theme */\n";
   $css .= $lightTheme->toCssVariables('light-');
   $css .= "\n/* Dark theme */\n";
   $css .= $darkTheme->toCssVariables('dark-');
   ```

3. [Performance Optimization](advanced/optimization.md)
   ```php
   use Farzai\ColorPalette\ColorExtractorFactory;
   use Farzai\ColorPalette\ImageLoader;
   
   // Optimize for large images
   ini_set('memory_limit', '256M');
   
   $loader = new ImageLoader('gd'); // Use GD for better performance
   $image = $loader->load('large-image.jpg');
   
   $factory = new ColorExtractorFactory();
   $extractor = $factory->create('gd')
       ->setMaxColors(5)    // Limit colors
       ->setQuality(50);    // Lower quality for speed
   
   try {
       $colors = $extractor->extract($image);
   } finally {
       $image->destroy(); // Clean up
   }
   ```

## Integration Examples

1. [Laravel Integration](integration/laravel.md)
   - Service provider setup
   - Facade implementation
   - Blade components

2. [Symfony Integration](integration/symfony.md)
   - Bundle configuration
   - Service definitions
   - Twig extensions

3. [WordPress Integration](integration/wordpress.md)
   - Plugin structure
   - Theme customizer integration
   - Dynamic CSS generation

## Testing Examples

1. [Unit Testing](testing/unit-tests.md)
   ```php
   use PHPUnit\Framework\TestCase;
   use Farzai\ColorPalette\Color;
   
   class ColorTest extends TestCase
   {
       public function testColorConversion()
       {
           $color = Color::fromHex('#2196f3');
           
           $this->assertEquals('#2196f3', $color->toHex());
           $this->assertEquals('rgb(33, 150, 243)', $color->toRgb());
       }
   }
   ```

2. [Feature Testing](testing/feature-tests.md)
   ```php
   use PHPUnit\Framework\TestCase;
   use Farzai\ColorPalette\ColorPaletteFactory;
   
   class PaletteGenerationTest extends TestCase
   {
       public function testPaletteGeneration()
       {
           $factory = new ColorPaletteFactory();
           $palette = $factory->createFromPath(__DIR__ . '/fixtures/test.jpg');
           
           $this->assertCount(5, $palette->getColors());
       }
   }
   ```

## Contributing Examples

If you have created an interesting example of using Color Palette PHP, please consider contributing it to this collection. See our [Contributing Guidelines](../CONTRIBUTING.md) for more information. 