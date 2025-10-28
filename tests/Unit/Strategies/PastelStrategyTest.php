<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\PastelStrategy;

describe('PastelStrategy Basic Functionality', function () {
    test('it can generate a pastel palette', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it always generates exactly 5 colors', function () {
        $strategy = new PastelStrategy;

        $palette1 = $strategy->generate(new Color(255, 0, 0));
        $palette2 = $strategy->generate(new Color(0, 255, 0));
        $palette3 = $strategy->generate(new Color(128, 128, 128));

        expect($palette1->count())->toBe(5);
        expect($palette2->count())->toBe(5);
        expect($palette3->count())->toBe(5);
    });

    test('it generates colors based on base color hue', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $baseHsl = $baseColor->toHsl();
        $firstColorHsl = $colors[0]->toHsl();

        // First color should have same hue as base color
        expect(abs($firstColorHsl['h'] - $baseHsl['h']))->toBeLessThan(2);
    });

    test('it creates pastel colors with consistent properties', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // All colors should be different
        $hexValues = array_map(fn ($color) => $color->toHex(), $colors);
        $uniqueHexValues = array_unique($hexValues);
        expect(count($uniqueHexValues))->toBe(5);
    });
});

describe('PastelStrategy Color Theory', function () {
    test('it generates colors with low saturation (25%)', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // All pastel colors should have saturation of 25%
            expect($hsl['s'])->toBe(25);
        }
    });

    test('it generates colors with high lightness (90%)', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // All pastel colors should have lightness of 90%
            expect($hsl['l'])->toBe(90);
        }
    });

    test('it generates colors 72 degrees apart on color wheel', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $baseHsl = $baseColor->toHsl();

        for ($i = 0; $i < 5; $i++) {
            $colorHsl = $colors[$i]->toHsl();
            $expectedHue = ($baseHsl['h'] + ($i * 72)) % 360;

            // Check hue is approximately correct (allow 1-2 degrees tolerance)
            $hueDiff = abs($colorHsl['h'] - $expectedHue);
            expect($hueDiff)->toBeLessThanOrEqual(2);
        }
    });

    test('it creates soft muted colors', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // Pastel colors should have low saturation and high lightness
            expect($hsl['s'])->toBeLessThanOrEqual(30);
            expect($hsl['l'])->toBeGreaterThanOrEqual(85);
        }
    });

    test('it maintains consistent saturation and lightness across all colors', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $firstHsl = $colors[0]->toHsl();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // All colors should have same saturation and lightness
            expect($hsl['s'])->toBe($firstHsl['s']);
            expect($hsl['l'])->toBe($firstHsl['l']);
        }
    });
});

describe('PastelStrategy Edge Cases', function () {
    test('it works with red color', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it works with green color', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(0, 255, 0); // Green

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it works with blue color', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(0, 0, 255); // Blue

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it works with black color', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(0, 0, 0); // Black

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it works with white color', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(255, 255, 255); // White

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it works with gray color', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(128, 128, 128); // Gray

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it handles hue wraparound correctly', function () {
        $strategy = new PastelStrategy;
        // Color with high hue (near 360)
        $baseColor = Color::fromHsl(350, 80, 50);

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
        // Should handle wraparound without errors
    });
});

describe('PastelStrategy Options', function () {
    test('it handles empty options array', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, []);

        expect($palette->count())->toBe(5);
    });

    test('it ignores count option', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(100, 150, 200);

        // Even if count is specified, pastel always returns 5 colors
        $palette = $strategy->generate($baseColor, ['count' => 10]);

        expect($palette->count())->toBe(5);
    });

    test('it ignores unknown options', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, [
            'unknown_option' => 'some_value',
            'another_option' => 123,
        ]);

        expect($palette->count())->toBe(5);
    });
});

describe('PastelStrategy Multiple Invocations', function () {
    test('it produces consistent results for same inputs', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette1 = $strategy->generate($baseColor);
        $palette2 = $strategy->generate($baseColor);

        expect($palette1->toArray())->toBe($palette2->toArray());
    });

    test('it generates different palettes for different base colors', function () {
        $strategy = new PastelStrategy;
        $baseColor1 = new Color(255, 0, 0); // Red
        $baseColor2 = new Color(0, 255, 0); // Green

        $palette1 = $strategy->generate($baseColor1);
        $palette2 = $strategy->generate($baseColor2);

        expect($palette1->toArray())->not->toBe($palette2->toArray());
    });

    test('it generates different palettes for colors with different hues', function () {
        $strategy = new PastelStrategy;
        $baseColor1 = Color::fromHsl(0, 50, 50);   // Red hue
        $baseColor2 = Color::fromHsl(120, 50, 50); // Green hue

        $palette1 = $strategy->generate($baseColor1);
        $palette2 = $strategy->generate($baseColor2);

        expect($palette1->toArray())->not->toBe($palette2->toArray());
    });
});

describe('PastelStrategy Color Validation', function () {
    test('it generates valid RGB colors', function () {
        $strategy = new PastelStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            expect($color)->toBeInstanceOf(Color::class);
            expect($color->getRed())->toBeGreaterThanOrEqual(0);
            expect($color->getRed())->toBeLessThanOrEqual(255);
            expect($color->getGreen())->toBeGreaterThanOrEqual(0);
            expect($color->getGreen())->toBeLessThanOrEqual(255);
            expect($color->getBlue())->toBeGreaterThanOrEqual(0);
            expect($color->getBlue())->toBeLessThanOrEqual(255);
        }
    });
});
