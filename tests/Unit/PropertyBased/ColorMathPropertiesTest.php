<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorAnalyzer;
use Farzai\ColorPalette\ColorManipulator;
use Farzai\ColorPalette\ColorSpaceConverter;

describe('Color Math Properties - Round Trip Conversions', function () {
    test('RGB to HSL to RGB round trip preserves color', function () {
        $testColors = [
            new Color(0, 0, 0),       // Black
            new Color(255, 255, 255), // White
            new Color(255, 0, 0),     // Red
            new Color(0, 255, 0),     // Green
            new Color(0, 0, 255),     // Blue
            new Color(128, 128, 128), // Gray
            new Color(100, 150, 200), // Random
            new Color(200, 100, 50),  // Random
            new Color(50, 200, 100),  // Random
        ];

        foreach ($testColors as $original) {
            $hsl = ColorSpaceConverter::toHsl($original);
            $converted = ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);

            // Allow small rounding errors (±2)
            expect(abs($original->getRed() - $converted->getRed()))->toBeLessThanOrEqual(2);
            expect(abs($original->getGreen() - $converted->getGreen()))->toBeLessThanOrEqual(2);
            expect(abs($original->getBlue() - $converted->getBlue()))->toBeLessThanOrEqual(2);
        }
    });

    test('RGB to HSV to RGB round trip preserves color', function () {
        $testColors = [
            new Color(255, 0, 0),
            new Color(0, 255, 0),
            new Color(0, 0, 255),
            new Color(100, 150, 200),
            new Color(200, 100, 50),
        ];

        foreach ($testColors as $original) {
            $hsv = ColorSpaceConverter::toHsv($original);
            $converted = ColorSpaceConverter::fromHsv($hsv['h'], $hsv['s'], $hsv['v']);

            expect(abs($original->getRed() - $converted->getRed()))->toBeLessThanOrEqual(2);
            expect(abs($original->getGreen() - $converted->getGreen()))->toBeLessThanOrEqual(2);
            expect(abs($original->getBlue() - $converted->getBlue()))->toBeLessThanOrEqual(2);
        }
    });

    test('RGB to CMYK to RGB round trip preserves color', function () {
        $testColors = [
            new Color(255, 0, 0),
            new Color(0, 255, 0),
            new Color(0, 0, 255),
            new Color(100, 150, 200),
        ];

        foreach ($testColors as $original) {
            $cmyk = ColorSpaceConverter::toCmyk($original);
            $converted = ColorSpaceConverter::fromCmyk($cmyk['c'], $cmyk['m'], $cmyk['y'], $cmyk['k']);

            expect(abs($original->getRed() - $converted->getRed()))->toBeLessThanOrEqual(3);
            expect(abs($original->getGreen() - $converted->getGreen()))->toBeLessThanOrEqual(3);
            expect(abs($original->getBlue() - $converted->getBlue()))->toBeLessThanOrEqual(3);
        }
    });

    test('RGB to LAB to RGB round trip preserves color', function () {
        $testColors = [
            new Color(0, 0, 0),
            new Color(255, 255, 255),
            new Color(128, 128, 128),
            new Color(100, 150, 200),
        ];

        foreach ($testColors as $original) {
            $lab = ColorSpaceConverter::toLab($original);
            $converted = ColorSpaceConverter::fromLab($lab['l'], $lab['a'], $lab['b']);

            // LAB conversions may have slightly larger tolerances
            expect(abs($original->getRed() - $converted->getRed()))->toBeLessThanOrEqual(5);
            expect(abs($original->getGreen() - $converted->getGreen()))->toBeLessThanOrEqual(5);
            expect(abs($original->getBlue() - $converted->getBlue()))->toBeLessThanOrEqual(5);
        }
    });
});

describe('Color Math Properties - Manipulation Reversibility', function () {
    test('lighten and darken by same amount are approximately inverse operations', function () {
        $testColors = [
            new Color(128, 100, 150),
            new Color(200, 50, 100),
            new Color(80, 160, 200),
        ];

        foreach ($testColors as $original) {
            $lightened = ColorManipulator::lighten($original, 0.2);
            $restored = ColorManipulator::darken($lightened, 0.2);

            expect(abs($original->getRed() - $restored->getRed()))->toBeLessThanOrEqual(5);
            expect(abs($original->getGreen() - $restored->getGreen()))->toBeLessThanOrEqual(5);
            expect(abs($original->getBlue() - $restored->getBlue()))->toBeLessThanOrEqual(5);
        }
    });

    test('saturate and desaturate by same amount are approximately inverse operations', function () {
        $testColors = [
            new Color(128, 100, 150),
            new Color(200, 50, 100),
        ];

        foreach ($testColors as $original) {
            $saturated = ColorManipulator::saturate($original, 0.3);
            $restored = ColorManipulator::desaturate($saturated, 0.3);

            expect(abs($original->getRed() - $restored->getRed()))->toBeLessThanOrEqual(5);
            expect(abs($original->getGreen() - $restored->getGreen()))->toBeLessThanOrEqual(5);
            expect(abs($original->getBlue() - $restored->getBlue()))->toBeLessThanOrEqual(5);
        }
    });

    test('rotate hue by 360 degrees returns to original', function () {
        $testColors = [
            new Color(255, 0, 0),
            new Color(128, 100, 150),
            new Color(200, 50, 100),
        ];

        foreach ($testColors as $original) {
            $rotated = ColorManipulator::rotate($original, 360);

            expect($rotated->toHex())->toBe($original->toHex());
        }
    });

    test('rotate hue and rotate back returns approximately to original', function () {
        $testColors = [
            new Color(255, 0, 0),
            new Color(128, 100, 150),
        ];

        foreach ($testColors as $original) {
            $rotated = ColorManipulator::rotate($original, 120);
            $restored = ColorManipulator::rotate($rotated, -120);

            expect(abs($original->getRed() - $restored->getRed()))->toBeLessThanOrEqual(3);
            expect(abs($original->getGreen() - $restored->getGreen()))->toBeLessThanOrEqual(3);
            expect(abs($original->getBlue() - $restored->getBlue()))->toBeLessThanOrEqual(3);
        }
    });

    test('double inversion returns to original', function () {
        $testColors = [
            new Color(255, 0, 0),
            new Color(128, 100, 150),
            new Color(200, 50, 100),
            new Color(0, 255, 128),
        ];

        foreach ($testColors as $original) {
            $inverted = ColorManipulator::invert($original);
            $restored = ColorManipulator::invert($inverted);

            expect($restored->toHex())->toBe($original->toHex());
        }
    });
});

describe('Color Math Properties - Symmetry', function () {
    test('contrast ratio is symmetric', function () {
        $color1 = new Color(100, 150, 200);
        $color2 = new Color(200, 100, 50);

        $ratio1 = ColorAnalyzer::getContrastRatio($color1, $color2);
        $ratio2 = ColorAnalyzer::getContrastRatio($color2, $color1);

        expect($ratio1)->toBe($ratio2);
    });

    test('RGB distance is symmetric', function () {
        $color1 = new Color(100, 150, 200);
        $color2 = new Color(200, 100, 50);

        $distance1 = ColorAnalyzer::getRgbDistance($color1, $color2);
        $distance2 = ColorAnalyzer::getRgbDistance($color2, $color1);

        expect($distance1)->toBe($distance2);
    });

    test('Delta E is symmetric', function () {
        $color1 = new Color(100, 150, 200);
        $color2 = new Color(200, 100, 50);

        $deltaE1 = ColorAnalyzer::getDeltaE($color1, $color2);
        $deltaE2 = ColorAnalyzer::getDeltaE($color2, $color1);

        expect(abs($deltaE1 - $deltaE2))->toBeLessThan(0.1);
    });

    test('color mixing with complementary weights produces same result', function () {
        $color1 = new Color(255, 0, 0);
        $color2 = new Color(0, 0, 255);

        $mix1 = ColorManipulator::mix($color1, $color2, 0.3);
        $mix2 = ColorManipulator::mix($color2, $color1, 0.7);

        expect($mix1->toHex())->toBe($mix2->toHex());
    });
});

describe('Color Math Properties - Boundary Constraints', function () {
    test('RGB values always stay within 0-255 range', function () {
        $testColors = [
            new Color(0, 0, 0),
            new Color(255, 255, 255),
            new Color(128, 100, 150),
        ];

        $operations = [
            fn ($c) => ColorManipulator::lighten($c, 0.5),
            fn ($c) => ColorManipulator::darken($c, 0.5),
            fn ($c) => ColorManipulator::saturate($c, 0.5),
            fn ($c) => ColorManipulator::desaturate($c, 0.5),
            fn ($c) => ColorManipulator::rotate($c, 180),
            fn ($c) => ColorManipulator::invert($c),
            fn ($c) => ColorManipulator::grayscale($c),
        ];

        foreach ($testColors as $color) {
            foreach ($operations as $operation) {
                $result = $operation($color);

                expect($result->getRed())->toBeGreaterThanOrEqual(0);
                expect($result->getRed())->toBeLessThanOrEqual(255);
                expect($result->getGreen())->toBeGreaterThanOrEqual(0);
                expect($result->getGreen())->toBeLessThanOrEqual(255);
                expect($result->getBlue())->toBeGreaterThanOrEqual(0);
                expect($result->getBlue())->toBeLessThanOrEqual(255);
            }
        }
    });

    test('HSL values stay within valid ranges', function () {
        $testColors = [
            new Color(255, 0, 0),
            new Color(128, 100, 150),
            new Color(200, 50, 100),
        ];

        foreach ($testColors as $color) {
            $hsl = ColorSpaceConverter::toHsl($color);

            expect($hsl['h'])->toBeGreaterThanOrEqual(0);
            expect($hsl['h'])->toBeLessThan(360);
            expect($hsl['s'])->toBeGreaterThanOrEqual(0);
            expect($hsl['s'])->toBeLessThanOrEqual(100);
            expect($hsl['l'])->toBeGreaterThanOrEqual(0);
            expect($hsl['l'])->toBeLessThanOrEqual(100);
        }
    });

    test('luminance stays within 0-1 range', function () {
        $testColors = [
            new Color(0, 0, 0),
            new Color(255, 255, 255),
            new Color(255, 0, 0),
            new Color(128, 100, 150),
        ];

        foreach ($testColors as $color) {
            $luminance = ColorAnalyzer::getLuminance($color);

            expect($luminance)->toBeGreaterThanOrEqual(0.0);
            expect($luminance)->toBeLessThanOrEqual(1.0);
        }
    });

    test('contrast ratio stays within 1-21 range', function () {
        $testColors = [
            [new Color(0, 0, 0), new Color(255, 255, 255)],
            [new Color(128, 128, 128), new Color(128, 128, 128)],
            [new Color(255, 0, 0), new Color(0, 0, 255)],
        ];

        foreach ($testColors as [$color1, $color2]) {
            $ratio = ColorAnalyzer::getContrastRatio($color1, $color2);

            expect($ratio)->toBeGreaterThanOrEqual(1.0);
            expect($ratio)->toBeLessThanOrEqual(21.0);
        }
    });
});

describe('Color Math Properties - Mathematical Relationships', function () {
    test('Delta E satisfies identity property', function () {
        $colors = [
            new Color(100, 150, 200),
            new Color(255, 0, 0),
            new Color(128, 128, 128),
        ];

        foreach ($colors as $color) {
            $deltaE = ColorAnalyzer::getDeltaE($color, $color);

            // Delta E of identical colors should be 0 or very close
            expect($deltaE)->toBeLessThan(0.1);
        }
    });

    test('Delta E satisfies triangle inequality approximately', function () {
        $color1 = new Color(100, 150, 200);
        $color2 = new Color(150, 125, 175);
        $color3 = new Color(200, 100, 150);

        $deltaE12 = ColorAnalyzer::getDeltaE($color1, $color2);
        $deltaE23 = ColorAnalyzer::getDeltaE($color2, $color3);
        $deltaE13 = ColorAnalyzer::getDeltaE($color1, $color3);

        // Triangle inequality: d(a,c) <= d(a,b) + d(b,c)
        // Allow some tolerance for rounding errors
        expect($deltaE13)->toBeLessThanOrEqual($deltaE12 + $deltaE23 + 1);
    });

    test('color mixing with weight 0 returns second color', function () {
        $color1 = new Color(255, 0, 0);
        $color2 = new Color(0, 0, 255);

        $mixed = ColorManipulator::mix($color1, $color2, 0.0);

        expect($mixed->toHex())->toBe($color2->toHex());
    });

    test('color mixing with weight 1 returns first color', function () {
        $color1 = new Color(255, 0, 0);
        $color2 = new Color(0, 0, 255);

        $mixed = ColorManipulator::mix($color1, $color2, 1.0);

        expect($mixed->toHex())->toBe($color1->toHex());
    });

    test('mixing a color with itself returns same color', function () {
        $colors = [
            new Color(100, 150, 200),
            new Color(255, 0, 0),
        ];

        foreach ($colors as $color) {
            $mixed = ColorManipulator::mix($color, $color, 0.5);

            expect($mixed->toHex())->toBe($color->toHex());
        }
    });

    test('maximum contrast ratio is between black and white', function () {
        $black = new Color(0, 0, 0);
        $white = new Color(255, 255, 255);

        $maxRatio = ColorAnalyzer::getContrastRatio($black, $white);

        $testPairs = [
            [new Color(255, 0, 0), new Color(0, 255, 0)],
            [new Color(100, 100, 100), new Color(200, 200, 200)],
            [new Color(50, 100, 150), new Color(200, 150, 100)],
        ];

        foreach ($testPairs as [$c1, $c2]) {
            $ratio = ColorAnalyzer::getContrastRatio($c1, $c2);
            expect($ratio)->toBeLessThanOrEqual($maxRatio);
        }
    });

    test('minimum contrast ratio is between identical colors', function () {
        $colors = [
            new Color(100, 150, 200),
            new Color(255, 0, 0),
            new Color(128, 128, 128),
        ];

        foreach ($colors as $color) {
            $ratio = ColorAnalyzer::getContrastRatio($color, $color);

            // Contrast ratio of identical colors should be very close to 1.0
            expect(abs($ratio - 1.0))->toBeLessThan(0.01);
        }
    });
});

describe('Color Math Properties - Monotonicity', function () {
    test('lighten with increasing amounts produces monotonically lighter colors', function () {
        $color = new Color(100, 100, 100);

        $colors = [
            $color,
            ColorManipulator::lighten($color, 0.1),
            ColorManipulator::lighten($color, 0.2),
            ColorManipulator::lighten($color, 0.3),
        ];

        for ($i = 0; $i < count($colors) - 1; $i++) {
            $hsl1 = ColorSpaceConverter::toHsl($colors[$i]);
            $hsl2 = ColorSpaceConverter::toHsl($colors[$i + 1]);

            expect($hsl2['l'])->toBeGreaterThanOrEqual($hsl1['l']);
        }
    });

    test('darken with increasing amounts produces monotonically darker colors', function () {
        $color = new Color(200, 200, 200);

        $colors = [
            $color,
            ColorManipulator::darken($color, 0.1),
            ColorManipulator::darken($color, 0.2),
            ColorManipulator::darken($color, 0.3),
        ];

        for ($i = 0; $i < count($colors) - 1; $i++) {
            $hsl1 = ColorSpaceConverter::toHsl($colors[$i]);
            $hsl2 = ColorSpaceConverter::toHsl($colors[$i + 1]);

            expect($hsl2['l'])->toBeLessThanOrEqual($hsl1['l']);
        }
    });

    test('saturate with increasing amounts produces monotonically more saturated colors', function () {
        $color = new Color(150, 120, 130);

        $colors = [
            $color,
            ColorManipulator::saturate($color, 0.1),
            ColorManipulator::saturate($color, 0.2),
            ColorManipulator::saturate($color, 0.3),
        ];

        for ($i = 0; $i < count($colors) - 1; $i++) {
            $hsl1 = ColorSpaceConverter::toHsl($colors[$i]);
            $hsl2 = ColorSpaceConverter::toHsl($colors[$i + 1]);

            expect($hsl2['s'])->toBeGreaterThanOrEqual($hsl1['s']);
        }
    });
});

describe('Color Math Properties - Grayscale Properties', function () {
    test('grayscale colors have zero saturation', function () {
        $colors = [
            new Color(255, 0, 0),
            new Color(0, 255, 0),
            new Color(100, 150, 200),
        ];

        foreach ($colors as $color) {
            $gray = ColorManipulator::grayscale($color);
            $hsl = ColorSpaceConverter::toHsl($gray);

            expect($hsl['s'])->toBe(0);
        }
    });

    test('grayscale preserves perceived brightness', function () {
        $colors = [
            new Color(255, 0, 0),
            new Color(0, 255, 0),
            new Color(100, 150, 200),
        ];

        foreach ($colors as $color) {
            $gray = ColorManipulator::grayscale($color);

            $originalL = ColorSpaceConverter::toHsl($color)['l'];
            $grayL = ColorSpaceConverter::toHsl($gray)['l'];

            // Lightness should be approximately the same
            expect(abs($originalL - $grayL))->toBeLessThanOrEqual(5);
        }
    });

    test('grayscale of grayscale returns same color', function () {
        $colors = [
            new Color(100, 100, 100),
            new Color(200, 200, 200),
        ];

        foreach ($colors as $color) {
            $gray1 = ColorManipulator::grayscale($color);
            $gray2 = ColorManipulator::grayscale($gray1);

            expect($gray1->toHex())->toBe($gray2->toHex());
        }
    });
});
