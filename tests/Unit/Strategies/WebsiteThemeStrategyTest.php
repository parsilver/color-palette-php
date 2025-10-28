<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\WebsiteThemeStrategy;

describe('WebsiteThemeStrategy Basic Functionality', function () {
    test('it can generate a website theme palette', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it always generates exactly 5 colors', function () {
        $strategy = new WebsiteThemeStrategy;

        $palette1 = $strategy->generate(new Color(255, 0, 0));
        $palette2 = $strategy->generate(new Color(0, 255, 0));
        $palette3 = $strategy->generate(new Color(128, 128, 128));

        expect($palette1->count())->toBe(5);
        expect($palette2->count())->toBe(5);
        expect($palette3->count())->toBe(5);
    });

    test('it includes base color as primary color', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        expect($colors['primary'])->toBe($baseColor);
    });

    test('it generates all required semantic color keys', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        expect($colors)->toHaveKey('primary');
        expect($colors)->toHaveKey('secondary');
        expect($colors)->toHaveKey('accent');
        expect($colors)->toHaveKey('background');
        expect($colors)->toHaveKey('surface');
    });
});

describe('WebsiteThemeStrategy Color Theory', function () {
    test('it generates secondary color 30 degrees from primary', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $primaryHsl = $colors['primary']->toHsl();
        $secondaryHsl = $colors['secondary']->toHsl();

        // Secondary should be 30 degrees from primary
        $diff = $secondaryHsl['h'] - $primaryHsl['h'];
        if ($diff < 0) {
            $diff += 360;
        }
        expect($diff)->toBeGreaterThanOrEqual(29);
        expect($diff)->toBeLessThanOrEqual(31);
    });

    test('it generates secondary with reduced saturation', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $primaryHsl = $colors['primary']->toHsl();
        $secondaryHsl = $colors['secondary']->toHsl();

        // Secondary should have lower saturation than primary
        expect($secondaryHsl['s'])->toBeLessThan($primaryHsl['s']);
    });

    test('it generates accent as complement with increased saturation', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $primaryHsl = $colors['primary']->toHsl();
        $accentHsl = $colors['accent']->toHsl();

        // Accent should be 180 degrees from primary (complement)
        $diff = abs($primaryHsl['h'] - $accentHsl['h']);
        expect($diff)->toBeGreaterThanOrEqual(179);
        expect($diff)->toBeLessThanOrEqual(181);

        // Accent should have higher saturation than primary
        expect($accentHsl['s'])->toBeGreaterThanOrEqual($primaryHsl['s']);
    });

    test('it generates near-white background color', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $backgroundHsl = $colors['background']->toHsl();

        // Background should have very high lightness (98%)
        expect($backgroundHsl['l'])->toBe(98);
        // Background should have no saturation (neutral)
        expect($backgroundHsl['s'])->toBe(0);
    });

    test('it generates pure white surface color', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $surfaceHsl = $colors['surface']->toHsl();

        // Surface should be pure white (lightness 100%)
        expect($surfaceHsl['l'])->toBe(100);
        // Surface should have no saturation (neutral)
        expect($surfaceHsl['s'])->toBe(0);
    });

    test('it creates a complete cohesive theme', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // All colors should be Color instances
        foreach ($colors as $colorName => $color) {
            expect($color)->toBeInstanceOf(Color::class);
        }

        // Primary and secondary should be related
        $primaryHsl = $colors['primary']->toHsl();
        $secondaryHsl = $colors['secondary']->toHsl();
        $hueDiff = abs($primaryHsl['h'] - $secondaryHsl['h']);
        expect($hueDiff)->toBeGreaterThan(0);
        expect($hueDiff)->toBeLessThan(90);
    });
});

describe('WebsiteThemeStrategy Edge Cases', function () {
    test('it works with red color', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
        expect($palette->getColors()['primary']->toHex())->toBe('#ff0000');
    });

    test('it works with green color', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(0, 255, 0); // Green

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
        expect($palette->getColors()['primary']->toHex())->toBe('#00ff00');
    });

    test('it works with blue color', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(0, 0, 255); // Blue

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
        expect($palette->getColors()['primary']->toHex())->toBe('#0000ff');
    });

    test('it works with black color', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(0, 0, 0); // Black

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it works with white color', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(255, 255, 255); // White

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it works with gray color', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(128, 128, 128); // Gray

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it handles hue wraparound correctly', function () {
        $strategy = new WebsiteThemeStrategy;
        // Color near red (hue near 0/360)
        $baseColor = Color::fromHsl(5, 80, 50);

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
        // Should handle wraparound without errors
    });

    test('it always generates same neutral background and surface', function () {
        $strategy = new WebsiteThemeStrategy;

        $palette1 = $strategy->generate(new Color(255, 0, 0));
        $palette2 = $strategy->generate(new Color(0, 255, 0));

        $colors1 = $palette1->getColors();
        $colors2 = $palette2->getColors();

        // Background and surface should be same regardless of base color
        expect($colors1['background']->toHex())->toBe($colors2['background']->toHex());
        expect($colors1['surface']->toHex())->toBe($colors2['surface']->toHex());
    });
});

describe('WebsiteThemeStrategy Options', function () {
    test('it handles empty options array', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, []);

        expect($palette->count())->toBe(5);
    });

    test('it ignores count option', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(100, 150, 200);

        // Even if count is specified, website theme always returns 5 colors
        $palette = $strategy->generate($baseColor, ['count' => 10]);

        expect($palette->count())->toBe(5);
    });

    test('it ignores unknown options', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, [
            'unknown_option' => 'some_value',
            'another_option' => 123,
        ]);

        expect($palette->count())->toBe(5);
    });
});

describe('WebsiteThemeStrategy Multiple Invocations', function () {
    test('it produces consistent results for same inputs', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette1 = $strategy->generate($baseColor);
        $palette2 = $strategy->generate($baseColor);

        expect($palette1->toArray())->toBe($palette2->toArray());
    });

    test('it generates different palettes for different base colors', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor1 = new Color(255, 0, 0); // Red
        $baseColor2 = new Color(0, 255, 0); // Green

        $palette1 = $strategy->generate($baseColor1);
        $palette2 = $strategy->generate($baseColor2);

        expect($palette1->toArray())->not->toBe($palette2->toArray());
    });

    test('it generates primary and secondary colors that differ for different bases', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor1 = new Color(255, 0, 0); // Red
        $baseColor2 = new Color(0, 0, 255); // Blue

        $palette1 = $strategy->generate($baseColor1);
        $palette2 = $strategy->generate($baseColor2);

        $colors1 = $palette1->getColors();
        $colors2 = $palette2->getColors();

        // Primary colors should be different
        expect($colors1['primary']->toHex())->not->toBe($colors2['primary']->toHex());
        // Secondary colors should be different
        expect($colors1['secondary']->toHex())->not->toBe($colors2['secondary']->toHex());
    });
});

describe('WebsiteThemeStrategy Color Validation', function () {
    test('it generates valid RGB colors', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        foreach ($colors as $colorName => $color) {
            expect($color)->toBeInstanceOf(Color::class);
            expect($color->getRed())->toBeGreaterThanOrEqual(0);
            expect($color->getRed())->toBeLessThanOrEqual(255);
            expect($color->getGreen())->toBeGreaterThanOrEqual(0);
            expect($color->getGreen())->toBeLessThanOrEqual(255);
            expect($color->getBlue())->toBeGreaterThanOrEqual(0);
            expect($color->getBlue())->toBeLessThanOrEqual(255);
        }
    });

    test('it provides accessible color combinations', function () {
        $strategy = new WebsiteThemeStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // Background should be very light for contrast with text
        $backgroundHsl = $colors['background']->toHsl();
        expect($backgroundHsl['l'])->toBeGreaterThan(95);

        // Surface should be even lighter (white)
        $surfaceHsl = $colors['surface']->toHsl();
        expect($surfaceHsl['l'])->toBe(100);
    });
});
