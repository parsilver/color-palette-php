<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\SplitComplementaryStrategy;

describe('SplitComplementaryStrategy Basic Functionality', function () {
    test('it can generate a split-complementary palette', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(3);
    });

    test('it always generates exactly 3 colors', function () {
        $strategy = new SplitComplementaryStrategy;

        $palette1 = $strategy->generate(new Color(255, 0, 0));
        $palette2 = $strategy->generate(new Color(0, 255, 0));
        $palette3 = $strategy->generate(new Color(128, 128, 128));

        expect($palette1->count())->toBe(3);
        expect($palette2->count())->toBe(3);
        expect($palette3->count())->toBe(3);
    });

    test('it includes base color as first color', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        expect($colors[0])->toBe($baseColor);
    });

    test('it generates split complements at 150 and 210 degrees', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // Second color should be rotated 150 degrees
        $color2Expected = $baseColor->rotate(150);
        expect($colors[1]->toHex())->toBe($color2Expected->toHex());

        // Third color should be rotated 210 degrees
        $color3Expected = $baseColor->rotate(210);
        expect($colors[2]->toHex())->toBe($color3Expected->toHex());
    });
});

describe('SplitComplementaryStrategy Color Theory', function () {
    test('it generates colors at 0, 150, and 210 degrees', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $baseHsl = $colors[0]->toHsl();
        $color2Hsl = $colors[1]->toHsl();
        $color3Hsl = $colors[2]->toHsl();

        // Color 2 should be +150 degrees from base
        $diff2 = $color2Hsl['h'] - $baseHsl['h'];
        if ($diff2 < 0) {
            $diff2 += 360;
        }
        expect($diff2)->toBeGreaterThanOrEqual(149);
        expect($diff2)->toBeLessThanOrEqual(151);

        // Color 3 should be +210 degrees from base
        $diff3 = $color3Hsl['h'] - $baseHsl['h'];
        if ($diff3 < 0) {
            $diff3 += 360;
        }
        expect($diff3)->toBeGreaterThanOrEqual(209);
        expect($diff3)->toBeLessThanOrEqual(211);
    });

    test('it creates colors adjacent to complement', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(255, 0, 0); // Red, complement at 180°

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $color2Hsl = $colors[1]->toHsl();
        $color3Hsl = $colors[2]->toHsl();

        // Colors 2 and 3 should be 30° on either side of 180° (the complement)
        // So at 150° and 210°, which are 60° apart
        $diffBetween = abs($color3Hsl['h'] - $color2Hsl['h']);
        if ($diffBetween > 180) {
            $diffBetween = 360 - $diffBetween;
        }
        expect($diffBetween)->toBeGreaterThanOrEqual(59);
        expect($diffBetween)->toBeLessThanOrEqual(61);
    });

    test('it maintains saturation and lightness across colors', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(200, 100, 50);
        $baseHsl = $baseColor->toHsl();

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // Saturation and lightness should be similar (hue rotation only)
            expect(abs($hsl['s'] - $baseHsl['s']))->toBeLessThan(2);
            expect(abs($hsl['l'] - $baseHsl['l']))->toBeLessThan(2);
        }
    });

    test('it provides high contrast with more nuance than complementary', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // All three colors should be different
        $hexValues = array_map(fn ($color) => $color->toHex(), $colors);
        $uniqueHexValues = array_unique($hexValues);
        expect(count($uniqueHexValues))->toBe(3);
    });
});

describe('SplitComplementaryStrategy Edge Cases', function () {
    test('it works with red color', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
        expect($palette->getColors()[0]->toHex())->toBe('#ff0000');
    });

    test('it works with green color', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(0, 255, 0); // Green

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
        expect($palette->getColors()[0]->toHex())->toBe('#00ff00');
    });

    test('it works with blue color', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(0, 0, 255); // Blue

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
        expect($palette->getColors()[0]->toHex())->toBe('#0000ff');
    });

    test('it works with black color', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(0, 0, 0); // Black

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with white color', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(255, 255, 255); // White

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with gray color', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(128, 128, 128); // Gray

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it handles hue wraparound correctly', function () {
        $strategy = new SplitComplementaryStrategy;
        // Color near red (hue near 0/360)
        $baseColor = Color::fromHsl(5, 80, 50);

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
        // Should handle wraparound without errors
    });
});

describe('SplitComplementaryStrategy Options', function () {
    test('it handles empty options array', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, []);

        expect($palette->count())->toBe(3);
    });

    test('it ignores count option', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(100, 150, 200);

        // Even if count is specified, split-complementary always returns 3 colors
        $palette = $strategy->generate($baseColor, ['count' => 10]);

        expect($palette->count())->toBe(3);
    });

    test('it ignores unknown options', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, [
            'unknown_option' => 'some_value',
            'another_option' => 123,
        ]);

        expect($palette->count())->toBe(3);
    });
});

describe('SplitComplementaryStrategy Multiple Invocations', function () {
    test('it produces consistent results for same inputs', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette1 = $strategy->generate($baseColor);
        $palette2 = $strategy->generate($baseColor);

        expect($palette1->toArray())->toBe($palette2->toArray());
    });

    test('it generates different palettes for different base colors', function () {
        $strategy = new SplitComplementaryStrategy;
        $baseColor1 = new Color(255, 0, 0); // Red
        $baseColor2 = new Color(0, 255, 0); // Green

        $palette1 = $strategy->generate($baseColor1);
        $palette2 = $strategy->generate($baseColor2);

        expect($palette1->toArray())->not->toBe($palette2->toArray());
    });
});

describe('SplitComplementaryStrategy Color Validation', function () {
    test('it generates valid RGB colors', function () {
        $strategy = new SplitComplementaryStrategy;
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
