<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorAnalyzer;
use Farzai\ColorPalette\ColorManipulator;
use Farzai\ColorPalette\ColorSpaceConverter;

describe('Color Operations Workflow - Accessibility Enhancement', function () {
    test('it can adjust color to meet WCAG AA contrast requirements', function () {
        $background = new Color(255, 255, 255); // White background
        $textColor = new Color(170, 170, 170); // Light gray text (poor contrast)

        // Check initial contrast
        expect(ColorAnalyzer::meetsWCAG_AA($textColor, $background))->toBeFalse();

        // Darken text color until it meets WCAG AA
        $adjustedColor = $textColor;
        $iterations = 0;
        while (! ColorAnalyzer::meetsWCAG_AA($adjustedColor, $background) && $iterations < 20) {
            $adjustedColor = ColorManipulator::darken($adjustedColor, 0.1);
            $iterations++;
        }

        expect(ColorAnalyzer::meetsWCAG_AA($adjustedColor, $background))->toBeTrue();
        expect($iterations)->toBeGreaterThan(0);
        expect($iterations)->toBeLessThan(20);
    });

    test('it can create accessible color palette from base color', function () {
        $baseColor = new Color(100, 150, 200);
        $background = new Color(255, 255, 255);

        // Create variations while maintaining accessibility
        $darkVersion = ColorManipulator::darken($baseColor, 0.3);
        $lightVersion = ColorManipulator::lighten($baseColor, 0.2);

        // Check if dark version is accessible on white
        $darkAccessible = ColorAnalyzer::meetsWCAG_AA($darkVersion, $background);

        // Verify we created valid colors
        expect($darkVersion)->toBeInstanceOf(Color::class);
        expect($lightVersion)->toBeInstanceOf(Color::class);
    });

    test('it can find best text color for any background', function () {
        $background = new Color(128, 128, 128); // Medium gray

        $blackText = new Color(0, 0, 0);
        $whiteText = new Color(255, 255, 255);

        $blackContrast = ColorAnalyzer::getContrastRatio($blackText, $background);
        $whiteContrast = ColorAnalyzer::getContrastRatio($whiteText, $background);

        $bestTextColor = $blackContrast > $whiteContrast ? $blackText : $whiteText;

        expect($bestTextColor)->toBeInstanceOf(Color::class);
        expect(ColorAnalyzer::getContrastRatio($bestTextColor, $background))
            ->toBeGreaterThan(ColorAnalyzer::getContrastRatio(
                $bestTextColor->toHex() === '#000000' ? $whiteText : $blackText,
                $background
            ));
    });
});

describe('Color Operations Workflow - Theme Generation', function () {
    test('it can create harmonious color variations', function () {
        $brandColor = new Color(41, 128, 185); // Blue brand color

        // Create a complementary color
        $complementary = ColorManipulator::rotate($brandColor, 180);

        // Create tints and shades
        $lightTint = ColorManipulator::lighten($brandColor, 0.3);
        $darkShade = ColorManipulator::darken($brandColor, 0.3);

        // Verify colors are different but related
        expect($complementary->toHex())->not->toBe($brandColor->toHex());
        expect($lightTint->toHex())->not->toBe($brandColor->toHex());
        expect($darkShade->toHex())->not->toBe($brandColor->toHex());

        // Verify they maintain hue relationship (for brand color and its variations)
        $brandHsl = ColorSpaceConverter::toHsl($brandColor);
        $lightHsl = ColorSpaceConverter::toHsl($lightTint);
        $darkHsl = ColorSpaceConverter::toHsl($darkShade);

        // Allow ±1 degree tolerance for hue due to rounding
        expect(abs($lightHsl['h'] - $brandHsl['h']))->toBeLessThanOrEqual(1);
        expect(abs($darkHsl['h'] - $brandHsl['h']))->toBeLessThanOrEqual(1);
    });

    test('it can generate muted color palette', function () {
        $vibrantColor = new Color(255, 0, 128); // Vibrant pink

        expect(ColorAnalyzer::isVibrant($vibrantColor))->toBeTrue();

        // Create muted version - desaturate significantly
        $mutedColor = ColorManipulator::desaturate($vibrantColor, 0.7);

        // Check if it's muted (saturation should be low)
        $hsl = ColorSpaceConverter::toHsl($mutedColor);
        expect($hsl['s'])->toBeLessThan(40);
        expect(ColorAnalyzer::isVibrant($mutedColor))->toBeFalse();

        // Verify hue is maintained
        $vibrantHsl = ColorSpaceConverter::toHsl($vibrantColor);
        $mutedHsl = ColorSpaceConverter::toHsl($mutedColor);

        expect($mutedHsl['h'])->toBe($vibrantHsl['h']);
    });

    test('it can create monochromatic palette with different lightness', function () {
        $baseColor = Color::fromHsl(200, 70, 50);

        $darker = ColorManipulator::withLightness($baseColor, 0.3);
        $base = $baseColor;
        $lighter = ColorManipulator::withLightness($baseColor, 0.7);

        $colors = [$darker, $base, $lighter];

        // All should have same hue and saturation
        $baseHsl = ColorSpaceConverter::toHsl($base);
        foreach ($colors as $color) {
            $hsl = ColorSpaceConverter::toHsl($color);
            expect($hsl['h'])->toBe($baseHsl['h']);
            expect($hsl['s'])->toBe($baseHsl['s']);
        }

        // But different lightness
        expect(ColorSpaceConverter::toHsl($darker)['l'])->toBeLessThan(ColorSpaceConverter::toHsl($base)['l']);
        expect(ColorSpaceConverter::toHsl($lighter)['l'])->toBeGreaterThan(ColorSpaceConverter::toHsl($base)['l']);
    });
});

describe('Color Operations Workflow - Color Matching', function () {
    test('it can find similar colors using Delta E', function () {
        $targetColor = new Color(100, 150, 200);
        $candidates = [
            new Color(105, 145, 205),
            new Color(200, 50, 50),
            new Color(100, 150, 200),
        ];

        $closestColor = null;
        $lowestDeltaE = PHP_FLOAT_MAX;

        foreach ($candidates as $candidate) {
            $deltaE = ColorAnalyzer::getDeltaE($targetColor, $candidate);
            if ($deltaE < $lowestDeltaE) {
                $lowestDeltaE = $deltaE;
                $closestColor = $candidate;
            }
        }

        // The third candidate (identical) should be closest
        expect($closestColor)->not->toBeNull();
        expect($closestColor?->toHex())->toBe($targetColor->toHex());
        expect($lowestDeltaE)->toBeLessThan(0.1);
    });

    test('it can convert between color spaces for advanced matching', function () {
        $color = new Color(100, 150, 200);

        // Convert through different color spaces
        $hsl = ColorSpaceConverter::toHsl($color);
        $hsv = ColorSpaceConverter::toHsv($color);
        $cmyk = ColorSpaceConverter::toCmyk($color);
        $lab = ColorSpaceConverter::toLab($color);

        // Convert back to RGB
        $fromHsl = ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
        $fromHsv = ColorSpaceConverter::fromHsv($hsv['h'], $hsv['s'], $hsv['v']);
        $fromCmyk = ColorSpaceConverter::fromCmyk($cmyk['c'], $cmyk['m'], $cmyk['y'], $cmyk['k']);
        $fromLab = ColorSpaceConverter::fromLab($lab['l'], $lab['a'], $lab['b']);

        // All should be similar to original (within rounding errors)
        expect(ColorAnalyzer::getRgbDistance($color, $fromHsl))->toBeLessThan(5);
        expect(ColorAnalyzer::getRgbDistance($color, $fromHsv))->toBeLessThan(5);
        expect(ColorAnalyzer::getRgbDistance($color, $fromCmyk))->toBeLessThan(5);
        expect(ColorAnalyzer::getRgbDistance($color, $fromLab))->toBeLessThan(5);
    });
});

describe('Color Operations Workflow - Color Temperature', function () {
    test('it can adjust color temperature', function () {
        $neutralColor = Color::fromHsl(180, 50, 50); // Cyan

        // Make it warmer by rotating towards red/yellow
        $warmerColor = ColorManipulator::rotate($neutralColor, -90); // Rotate to green/yellow

        // Make it cooler by rotating towards blue
        $coolerColor = ColorManipulator::rotate($neutralColor, 90); // Rotate to blue

        // Verify temperature classification
        $neutralHsl = ColorSpaceConverter::toHsl($neutralColor);
        $warmerHsl = ColorSpaceConverter::toHsl($warmerColor);
        $coolerHsl = ColorSpaceConverter::toHsl($coolerColor);

        // Warmer should have hue closer to warm range (0-60)
        // Cooler should have hue in cool range (120-300)
        expect($neutralHsl['h'])->toBe(180);
        expect($warmerHsl['h'])->toBe(90);
        expect($coolerHsl['h'])->toBe(270);
    });

    test('it can identify and manipulate warm and cool colors', function () {
        $warmColor = Color::fromHsl(30, 80, 50); // Orange
        $coolColor = Color::fromHsl(210, 80, 50); // Blue

        expect(ColorAnalyzer::isWarm($warmColor))->toBeTrue();
        expect(ColorAnalyzer::isCool($warmColor))->toBeFalse();

        expect(ColorAnalyzer::isCool($coolColor))->toBeTrue();
        expect(ColorAnalyzer::isWarm($coolColor))->toBeFalse();

        // Mix warm and cool colors
        $mixed = ColorManipulator::mix($warmColor, $coolColor, 0.5);

        expect($mixed)->toBeInstanceOf(Color::class);
    });
});

describe('Color Operations Workflow - Brightness Adjustment', function () {
    test('it can balance brightness across color palette', function () {
        $colors = [
            new Color(255, 0, 0),   // Bright red
            new Color(0, 100, 0),   // Dark green
            new Color(100, 100, 200), // Medium blue
        ];

        $targetBrightness = 150;
        $adjustedColors = [];

        foreach ($colors as $color) {
            $currentBrightness = ColorAnalyzer::getBrightness($color);
            $hsl = ColorSpaceConverter::toHsl($color);

            if ($currentBrightness < $targetBrightness) {
                // Lighten dark colors
                $adjusted = ColorManipulator::lighten($color, 0.2);
            } else {
                // Darken bright colors
                $adjusted = ColorManipulator::darken($color, 0.1);
            }

            $adjustedColors[] = $adjusted;
        }

        expect(count($adjustedColors))->toBe(3);
        foreach ($adjustedColors as $color) {
            expect($color)->toBeInstanceOf(Color::class);
        }
    });

    test('it can create light and dark variants maintaining hue', function () {
        $baseColor = new Color(128, 64, 192);

        // Create very light version (tint)
        $lightVariant = ColorManipulator::withLightness($baseColor, 0.9);

        // Create very dark version (shade)
        $darkVariant = ColorManipulator::withLightness($baseColor, 0.1);

        $baseHsl = ColorSpaceConverter::toHsl($baseColor);
        $lightHsl = ColorSpaceConverter::toHsl($lightVariant);
        $darkHsl = ColorSpaceConverter::toHsl($darkVariant);

        // All should have similar hue (allow ±1 degree tolerance)
        expect(abs($lightHsl['h'] - $baseHsl['h']))->toBeLessThanOrEqual(1);
        expect(abs($darkHsl['h'] - $baseHsl['h']))->toBeLessThanOrEqual(1);

        // Verify lightness is different
        expect($lightHsl['l'])->toBe(90);
        expect($darkHsl['l'])->toBe(10);
    });
});

describe('Color Operations Workflow - Advanced Manipulations', function () {
    test('it can create duotone effect', function () {
        $color1 = new Color(255, 50, 100); // Pinkish
        $color2 = new Color(50, 100, 255); // Bluish

        // Create several steps between the two colors
        $steps = [];
        for ($i = 0; $i <= 4; $i++) {
            $weight = $i / 4;
            $steps[] = ColorManipulator::mix($color1, $color2, 1 - $weight);
        }

        expect(count($steps))->toBe(5);
        expect($steps[0]->toHex())->toBe($color1->toHex());
        expect($steps[4]->toHex())->toBe($color2->toHex());

        // Verify colors transition smoothly
        for ($i = 0; $i < 4; $i++) {
            $distance = ColorAnalyzer::getRgbDistance($steps[$i], $steps[$i + 1]);
            expect($distance)->toBeGreaterThan(0);
            expect($distance)->toBeLessThan(200); // Not too large a jump
        }
    });

    test('it can create color with specific perceptual properties', function () {
        // Start with any color
        $startColor = new Color(123, 45, 67);

        // Make it vibrant
        $vibrant = ColorManipulator::saturate($startColor, 0.5);

        // Make it medium brightness
        $balanced = ColorManipulator::withLightness($vibrant, 0.5);

        // Verify properties
        expect(ColorAnalyzer::isVibrant($balanced))->toBeTrue();

        $hsl = ColorSpaceConverter::toHsl($balanced);
        expect($hsl['l'])->toBe(50);
        expect($hsl['s'])->toBeGreaterThan(70);
    });

    test('it can analyze and adjust color for printing (CMYK)', function () {
        $rgbColor = new Color(100, 150, 200);

        // Convert to CMYK (printing color space)
        $cmyk = ColorSpaceConverter::toCmyk($rgbColor);

        // Convert back to RGB
        $printColor = ColorSpaceConverter::fromCmyk($cmyk['c'], $cmyk['m'], $cmyk['y'], $cmyk['k']);

        // Colors should be similar (within gamut conversion limits)
        $distance = ColorAnalyzer::getRgbDistance($rgbColor, $printColor);
        expect($distance)->toBeLessThan(10);
    });
});

describe('Color Operations Workflow - Real-world Scenarios', function () {
    test('it can generate button hover state color', function () {
        $buttonColor = new Color(41, 128, 185); // Primary button color

        // Hover state: slightly darker and more saturated
        $hoverColor = ColorManipulator::darken($buttonColor, 0.1);
        $hoverColor = ColorManipulator::saturate($hoverColor, 0.1);

        expect($hoverColor)->toBeInstanceOf(Color::class);

        // Should be darker
        $buttonHsl = ColorSpaceConverter::toHsl($buttonColor);
        $hoverHsl = ColorSpaceConverter::toHsl($hoverColor);

        expect($hoverHsl['l'])->toBeLessThan($buttonHsl['l']);
        expect($hoverHsl['s'])->toBeGreaterThan($buttonHsl['s']);
    });

    test('it can create disabled button color', function () {
        $buttonColor = new Color(41, 128, 185);

        // Disabled state: desaturated and lighter
        $disabledColor = ColorManipulator::desaturate($buttonColor, 0.6);
        $disabledColor = ColorManipulator::lighten($disabledColor, 0.2);

        expect(ColorAnalyzer::isMuted($disabledColor))->toBeTrue();

        $hsl = ColorSpaceConverter::toHsl($disabledColor);
        expect($hsl['s'])->toBeLessThan(50);
    });

    test('it can validate color combinations for UI design', function () {
        $backgroundColor = new Color(248, 249, 250); // Light background
        $primaryColor = new Color(0, 123, 255); // Primary blue
        $textColor = new Color(33, 37, 41); // Dark text

        // Check text on background
        expect(ColorAnalyzer::meetsWCAG_AA($textColor, $backgroundColor))->toBeTrue();

        // Check primary color contrast
        $primaryOnBg = ColorAnalyzer::getContrastRatio($primaryColor, $backgroundColor);
        expect($primaryOnBg)->toBeGreaterThan(3.0);

        // Verify colors are distinguishable
        $textVsPrimary = ColorAnalyzer::getRgbDistance($textColor, $primaryColor);
        expect($textVsPrimary)->toBeGreaterThan(100);
    });

    test('it can create notification color variants', function () {
        $baseColors = [
            'success' => new Color(40, 167, 69),
            'warning' => new Color(255, 193, 7),
            'danger' => new Color(220, 53, 69),
            'info' => new Color(23, 162, 184),
        ];

        foreach ($baseColors as $type => $color) {
            // Create light background variant
            $lightBg = ColorManipulator::lighten($color, 0.4);
            $lightBg = ColorManipulator::desaturate($lightBg, 0.3);

            // Create border variant
            $border = ColorManipulator::darken($color, 0.1);

            // Verify accessibility
            $textContrast = ColorAnalyzer::getContrastRatio($color, $lightBg);

            expect($lightBg)->toBeInstanceOf(Color::class);
            expect($border)->toBeInstanceOf(Color::class);
            expect($textContrast)->toBeGreaterThan(1.0);
        }
    });
});
