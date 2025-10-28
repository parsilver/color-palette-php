<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\ColorPaletteBuilder;
use Farzai\ColorPalette\Theme;
use Farzai\ColorPalette\ThemeGenerator;

describe('Complete Image to Palette Workflow', function () {
    test('it can extract colors from image using builder', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $palette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(5)
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);

        // Verify all colors are valid
        foreach ($palette->getColors() as $color) {
            expect($color)->toBeInstanceOf(Color::class);
            expect($color->getRed())->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(255);
            expect($color->getGreen())->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(255);
            expect($color->getBlue())->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(255);
        }
    });

    test('it can convert extracted palette to hex array', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $palette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(3)
            ->build();

        $hexArray = $palette->toArray();

        expect($hexArray)->toBeArray();
        expect($hexArray)->toHaveCount(3);

        foreach ($hexArray as $hex) {
            expect($hex)->toMatch('/^#[0-9a-f]{6}$/i');
        }
    });

    test('it can extract different color counts from same image', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $palette3 = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(3)
            ->build();

        $palette5 = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(5)
            ->build();

        $palette10 = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(10)
            ->build();

        expect($palette3->count())->toBe(3);
        expect($palette5->count())->toBe(5);
        expect($palette10->count())->toBe(10);
    });
});

describe('Builder with Image Extraction Workflow', function () {
    test('it can use builder to extract colors from image', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $palette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(7)
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(7);
    });

    test('it can chain image extraction with other builder methods', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $builder = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(5)
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('monochromatic');

        $palette = $builder->build();

        // Image extraction takes priority over strategy
        expect($palette->count())->toBe(5);
    });
});

describe('Builder with Strategy Workflow', function () {
    test('it can generate palette using builder with strategy', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(45, 140, 200))
            ->withScheme('monochromatic', ['count' => 7])
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(7);

        // All colors should have the same base hue (allowing for very light/dark colors where hue becomes undefined)
        $baseHue = $palette->getColors()[0]->toHsl()['h'];
        foreach ($palette->getColors() as $color) {
            $hsl = $color->toHsl();
            // For very light or very dark colors (where saturation approaches 0), hue is undefined
            // So we only check hue for colors with reasonable saturation
            if ($hsl['s'] > 5 && $hsl['l'] > 5 && $hsl['l'] < 95) {
                $hue = $hsl['h'];
                $diff = abs($hue - $baseHue);
                if ($diff > 180) {
                    $diff = 360 - $diff;
                }
                expect($diff)->toBeLessThan(5);
            }
        }
    });

    test('it can generate palette with multiple different strategies', function () {
        $baseColor = new Color(200, 50, 100);

        $strategies = [
            'monochromatic' => ['count' => 5],
            'complementary' => [],
            'analogous' => [],
            'triadic' => [],
            'tetradic' => [],
        ];

        foreach ($strategies as $strategyName => $options) {
            $palette = ColorPaletteBuilder::create()
                ->withBaseColor($baseColor)
                ->withScheme($strategyName, $options)
                ->build();

            expect($palette)->toBeInstanceOf(ColorPalette::class);
            expect($palette->count())->toBeGreaterThan(0);
        }
    });

    test('it can switch between strategies with same builder', function () {
        $builder = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(180, 90, 120));

        $monochromatic = $builder->withScheme('monochromatic', ['count' => 5])->build();
        $complementary = $builder->withScheme('complementary')->build();
        $triadic = $builder->withScheme('triadic')->build();

        expect($monochromatic->count())->toBe(5);
        expect($complementary->count())->toBe(2);
        expect($triadic->count())->toBe(3);
    });
});

describe('Builder with Manual Colors Workflow', function () {
    test('it can build palette from manually added colors', function () {
        $red = new Color(255, 0, 0);
        $green = new Color(0, 255, 0);
        $blue = new Color(0, 0, 255);

        $palette = ColorPaletteBuilder::create()
            ->addColor($red)
            ->addColor($green)
            ->addColor($blue)
            ->build();

        expect($palette->count())->toBe(3);
        expect($palette->getColors()[0])->toBe($red);
        expect($palette->getColors()[1])->toBe($green);
        expect($palette->getColors()[2])->toBe($blue);
    });

    test('it can build palette with named colors', function () {
        $palette = ColorPaletteBuilder::create()
            ->addColor(new Color(255, 0, 0), 'primary')
            ->addColor(new Color(0, 255, 0), 'secondary')
            ->addColor(new Color(0, 0, 255), 'accent')
            ->build();

        $colors = $palette->getColors();

        expect($colors['primary'])->toBeInstanceOf(Color::class);
        expect($colors['secondary'])->toBeInstanceOf(Color::class);
        expect($colors['accent'])->toBeInstanceOf(Color::class);
    });

    test('it can mix manual colors with addColors method', function () {
        $palette = ColorPaletteBuilder::create()
            ->addColor(new Color(255, 0, 0))
            ->addColors([
                new Color(0, 255, 0),
                new Color(0, 0, 255),
            ])
            ->addColor(new Color(255, 255, 0))
            ->build();

        expect($palette->count())->toBe(4);
    });
});

describe('Mixed Priority Workflow', function () {
    test('it properly handles priority when all methods are used', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $manualColor = new Color(128, 64, 32);

        // Add manual color (highest priority)
        $palette = ColorPaletteBuilder::create()
            ->addColor($manualColor)
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(10)
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('monochromatic', ['count' => 15])
            ->build();

        // Should only contain the manual color (priority 1)
        expect($palette->count())->toBe(1);
        expect($palette->getColors()[0])->toBe($manualColor);
    });

    test('it uses image extraction when no manual colors provided', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $palette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(6)
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('complementary')
            ->build();

        // Should use image extraction (priority 2)
        expect($palette->count())->toBe(6);
    });

    test('it uses strategy when only base color and strategy provided', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(180, 90, 45))
            ->withScheme('triadic')
            ->build();

        // Should use strategy (priority 3)
        expect($palette->count())->toBe(3);
    });
});

describe('Image to Theme Workflow', function () {
    test('it can extract colors from image and create theme', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Extract colors using ColorPaletteBuilder
        $palette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(5)
            ->build();

        $themeGenerator = new ThemeGenerator;
        $theme = $themeGenerator->generate($palette, ['primary', 'secondary', 'accent', 'background', 'surface']);

        expect($theme)->toBeInstanceOf(Theme::class);
        expect($theme->getPrimaryColor())->toBeInstanceOf(Color::class);
        expect($theme->getSecondaryColor())->toBeInstanceOf(Color::class);
    });

    test('it can create theme with convenience methods', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Extract colors using ColorPaletteBuilder
        $palette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(3)
            ->build();

        $themeGenerator = new ThemeGenerator;
        $theme = $themeGenerator->generate($palette, ['primary', 'secondary', 'accent']);

        $primary = $theme->getPrimaryColor();
        $secondary = $theme->getSecondaryColor();

        expect($primary)->toBeInstanceOf(Color::class);
        expect($secondary)->toBeInstanceOf(Color::class);
        // Note: Primary and secondary might be the same if the image has limited color variation
    });
});

describe('Strategy to Theme Workflow', function () {
    test('it can generate palette from strategy and create theme', function () {
        $baseColor = new Color(45, 120, 200);

        $palette = ColorPaletteBuilder::create()
            ->withBaseColor($baseColor)
            ->withScheme('website-theme')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);

        // Website theme strategy should create named colors
        $colors = $palette->getColors();
        expect(array_key_exists('primary', $colors))->toBeTrue();
        expect(array_key_exists('secondary', $colors))->toBeTrue();
        expect(array_key_exists('accent', $colors))->toBeTrue();
    });

    test('it can generate multiple color scheme variations', function () {
        $baseColor = new Color(200, 100, 50);

        $schemes = [
            'monochromatic' => 5,
            'analogous' => 3,
            'complementary' => 2,
            'triadic' => 3,
            'tetradic' => 4,
            'split-complementary' => 3,
        ];

        foreach ($schemes as $scheme => $expectedCount) {
            $palette = ColorPaletteBuilder::create()
                ->withBaseColor($baseColor)
                ->withScheme($scheme)
                ->build();

            expect($palette->count())->toBe($expectedCount);
        }
    });
});

describe('Complete End-to-End Workflows', function () {
    test('it can go from raw image to styled theme', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Step 1: Extract colors from image using ColorPaletteBuilder
        $extractedPalette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(5)
            ->build();

        expect($extractedPalette->count())->toBe(5);

        // Step 2: Get dominant color
        $dominantColor = $extractedPalette->getColors()[0];

        // Step 3: Generate harmonious palette from dominant color
        $harmoniousPalette = ColorPaletteBuilder::create()
            ->withBaseColor($dominantColor)
            ->withScheme('analogous')
            ->build();

        expect($harmoniousPalette->count())->toBe(3);

        // Step 4: Create theme
        $themeGenerator = new ThemeGenerator;
        $theme = $themeGenerator->generate($harmoniousPalette, ['primary', 'secondary', 'accent']);

        expect($theme)->toBeInstanceOf(Theme::class);
        expect($theme->getPrimaryColor())->toBeInstanceOf(Color::class);
    });

    test('it can create brand colors from single color', function () {
        $brandColor = new Color(230, 80, 50);

        // Create main palette
        $mainPalette = ColorPaletteBuilder::create()
            ->withBaseColor($brandColor)
            ->withScheme('analogous')
            ->build();

        // Create accent palette
        $accentPalette = ColorPaletteBuilder::create()
            ->withBaseColor($brandColor)
            ->withScheme('complementary')
            ->build();

        // Create neutral palette
        $neutralPalette = ColorPaletteBuilder::create()
            ->withBaseColor($brandColor)
            ->withScheme('shades', ['count' => 10])
            ->build();

        expect($mainPalette->count())->toBe(3);
        expect($accentPalette->count())->toBe(2);
        expect($neutralPalette->count())->toBe(10);
    });

    test('it can create complete UI color system', function () {
        $primaryColor = new Color(45, 90, 200);

        // Create website theme
        $themePalette = ColorPaletteBuilder::create()
            ->withBaseColor($primaryColor)
            ->withScheme('website-theme')
            ->build();

        expect($themePalette->count())->toBe(5);

        $colors = $themePalette->getColors();

        // Verify we have all necessary colors for a UI
        expect($colors['primary'])->toBeInstanceOf(Color::class);
        expect($colors['secondary'])->toBeInstanceOf(Color::class);
        expect($colors['accent'])->toBeInstanceOf(Color::class);
        expect($colors['background'])->toBeInstanceOf(Color::class);
        expect($colors['surface'])->toBeInstanceOf(Color::class);

        // Create additional color variations
        $primaryShades = ColorPaletteBuilder::create()
            ->withBaseColor($colors['primary'])
            ->withScheme('shades', ['count' => 5])
            ->build();

        $primaryTints = ColorPaletteBuilder::create()
            ->withBaseColor($colors['primary'])
            ->withScheme('tints', ['count' => 5])
            ->build();

        expect($primaryShades->count())->toBe(5);
        expect($primaryTints->count())->toBe(5);
    });

    test('it can validate color accessibility', function () {
        $backgroundColor = new Color(255, 255, 255);
        $textColor = new Color(0, 0, 0);

        $contrastRatio = $backgroundColor->getContrastRatio($textColor);

        // WCAG AA requires 4.5:1 for normal text, 3:1 for large text
        // WCAG AAA requires 7:1 for normal text, 4.5:1 for large text
        expect($contrastRatio)->toBeGreaterThan(4.5); // Should meet WCAG AA
    });
});

describe('Palette Reusability and Composition', function () {
    test('it can compose palettes from multiple sources', function () {
        $colors = [];

        // Add colors from a monochromatic scheme
        $mono = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(100, 150, 200))
            ->withScheme('monochromatic', ['count' => 3])
            ->build();

        foreach ($mono->getColors() as $color) {
            $colors[] = $color;
        }

        // Add a complementary color
        $comp = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(100, 150, 200))
            ->withScheme('complementary')
            ->build();

        $colors[] = $comp->getColors()[1]; // Add just the complement

        // Create final composed palette
        $composedPalette = ColorPaletteBuilder::create()
            ->addColors($colors)
            ->build();

        expect($composedPalette->count())->toBe(4); // 3 mono + 1 complement
    });

    test('it can extract and reuse dominant colors across workflows', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Extract from image using ColorPaletteBuilder
        $imagePalette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(5)
            ->build();

        $dominantColor = $imagePalette->getColors()[0];

        // Reuse in multiple palettes
        $palette1 = ColorPaletteBuilder::create()
            ->withBaseColor($dominantColor)
            ->withScheme('monochromatic', ['count' => 5])
            ->build();

        $palette2 = ColorPaletteBuilder::create()
            ->withBaseColor($dominantColor)
            ->withScheme('complementary')
            ->build();

        $palette3 = ColorPaletteBuilder::create()
            ->withBaseColor($dominantColor)
            ->withScheme('triadic')
            ->build();

        expect($palette1->count())->toBe(5);
        expect($palette2->count())->toBe(2);
        expect($palette3->count())->toBe(3);

        // All should have the dominant color
        expect($palette1->getColors()[0])->toBe($dominantColor);
        expect($palette2->getColors()[0])->toBe($dominantColor);
        expect($palette3->getColors()[0])->toBe($dominantColor);
    });
});
