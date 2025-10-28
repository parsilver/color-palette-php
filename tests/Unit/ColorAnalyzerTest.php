<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorAnalyzer;

describe('ColorAnalyzer Brightness Calculations', function () {
    test('it calculates brightness using ITU-R BT.601 formula', function () {
        $color = new Color(255, 0, 0); // Pure red

        $brightness = ColorAnalyzer::getBrightness($color);

        // (255 * 299 + 0 * 587 + 0 * 114) / 1000 = 76.245
        expect(abs($brightness - 76.245))->toBeLessThan(0.1);
    });

    test('it calculates brightness for pure green', function () {
        $color = new Color(0, 255, 0); // Pure green

        $brightness = ColorAnalyzer::getBrightness($color);

        // (0 * 299 + 255 * 587 + 0 * 114) / 1000 = 149.685
        expect(abs($brightness - 149.685))->toBeLessThan(0.1);
    });

    test('it calculates brightness for pure blue', function () {
        $color = new Color(0, 0, 255); // Pure blue

        $brightness = ColorAnalyzer::getBrightness($color);

        // (0 * 299 + 0 * 587 + 255 * 114) / 1000 = 29.07
        expect(abs($brightness - 29.07))->toBeLessThan(0.1);
    });

    test('it returns 0 brightness for black', function () {
        $color = new Color(0, 0, 0); // Black

        $brightness = ColorAnalyzer::getBrightness($color);

        expect($brightness)->toBe(0.0);
    });

    test('it returns 255 brightness for white', function () {
        $color = new Color(255, 255, 255); // White

        $brightness = ColorAnalyzer::getBrightness($color);

        expect($brightness)->toBe(255.0);
    });

    test('it calculates brightness for gray', function () {
        $color = new Color(128, 128, 128); // Medium gray

        $brightness = ColorAnalyzer::getBrightness($color);

        expect($brightness)->toBe(128.0);
    });
});

describe('ColorAnalyzer Light/Dark Classification', function () {
    test('it identifies white as light', function () {
        $color = new Color(255, 255, 255);

        expect(ColorAnalyzer::isLight($color))->toBeTrue();
        expect(ColorAnalyzer::isDark($color))->toBeFalse();
    });

    test('it identifies black as dark', function () {
        $color = new Color(0, 0, 0);

        expect(ColorAnalyzer::isLight($color))->toBeFalse();
        expect(ColorAnalyzer::isDark($color))->toBeTrue();
    });

    test('it uses threshold of 128 for classification', function () {
        $justBelowThreshold = new Color(127, 127, 127);
        $atThreshold = new Color(128, 128, 128);
        $justAboveThreshold = new Color(129, 129, 129);

        expect(ColorAnalyzer::isLight($justBelowThreshold))->toBeFalse();
        expect(ColorAnalyzer::isDark($justBelowThreshold))->toBeTrue();

        expect(ColorAnalyzer::isLight($atThreshold))->toBeFalse();
        expect(ColorAnalyzer::isDark($atThreshold))->toBeTrue();

        expect(ColorAnalyzer::isLight($justAboveThreshold))->toBeTrue();
        expect(ColorAnalyzer::isDark($justAboveThreshold))->toBeFalse();
    });

    test('it identifies pure red as dark', function () {
        $color = new Color(255, 0, 0);

        expect(ColorAnalyzer::isLight($color))->toBeFalse();
        expect(ColorAnalyzer::isDark($color))->toBeTrue();
    });

    test('it identifies pure green as light', function () {
        $color = new Color(0, 255, 0);

        expect(ColorAnalyzer::isLight($color))->toBeTrue();
        expect(ColorAnalyzer::isDark($color))->toBeFalse();
    });
});

describe('ColorAnalyzer Luminance Calculations', function () {
    test('it calculates luminance with gamma correction', function () {
        $white = new Color(255, 255, 255);

        $luminance = ColorAnalyzer::getLuminance($white);

        expect(abs($luminance - 1.0))->toBeLessThan(0.01);
    });

    test('it returns 0 luminance for black', function () {
        $black = new Color(0, 0, 0);

        $luminance = ColorAnalyzer::getLuminance($black);

        expect($luminance)->toBe(0.0);
    });

    test('it calculates luminance for pure red', function () {
        $red = new Color(255, 0, 0);

        $luminance = ColorAnalyzer::getLuminance($red);

        // Red should have luminance around 0.2126
        expect(abs($luminance - 0.2126))->toBeLessThan(0.01);
    });

    test('it calculates luminance for pure green', function () {
        $green = new Color(0, 255, 0);

        $luminance = ColorAnalyzer::getLuminance($green);

        // Green should have luminance around 0.7152
        expect(abs($luminance - 0.7152))->toBeLessThan(0.01);
    });

    test('it calculates luminance for pure blue', function () {
        $blue = new Color(0, 0, 255);

        $luminance = ColorAnalyzer::getLuminance($blue);

        // Blue should have luminance around 0.0722
        expect(abs($luminance - 0.0722))->toBeLessThan(0.01);
    });

    test('it calculates luminance for medium gray', function () {
        $gray = new Color(128, 128, 128);

        $luminance = ColorAnalyzer::getLuminance($gray);

        // Medium gray should be around 0.21-0.22
        expect($luminance)->toBeGreaterThan(0.2);
        expect($luminance)->toBeLessThan(0.23);
    });
});

describe('ColorAnalyzer Contrast Ratio', function () {
    test('it calculates maximum contrast ratio for black and white', function () {
        $black = new Color(0, 0, 0);
        $white = new Color(255, 255, 255);

        $ratio = ColorAnalyzer::getContrastRatio($black, $white);

        // Maximum contrast ratio is 21:1
        expect(abs($ratio - 21.0))->toBeLessThan(0.1);
    });

    test('it calculates minimum contrast ratio for identical colors', function () {
        $color = new Color(128, 128, 128);

        $ratio = ColorAnalyzer::getContrastRatio($color, $color);

        // Minimum contrast ratio is 1:1
        expect(abs($ratio - 1.0))->toBeLessThan(0.01);
    });

    test('it returns same contrast ratio regardless of order', function () {
        $color1 = new Color(255, 0, 0);
        $color2 = new Color(0, 0, 255);

        $ratio1 = ColorAnalyzer::getContrastRatio($color1, $color2);
        $ratio2 = ColorAnalyzer::getContrastRatio($color2, $color1);

        expect($ratio1)->toBe($ratio2);
    });

    test('it calculates contrast for red and blue', function () {
        $red = new Color(255, 0, 0);
        $blue = new Color(0, 0, 255);

        $ratio = ColorAnalyzer::getContrastRatio($red, $blue);

        expect($ratio)->toBeGreaterThan(1.0);
        expect($ratio)->toBeLessThan(5.0);
    });

    test('it calculates contrast within valid range', function () {
        $color1 = new Color(100, 150, 200);
        $color2 = new Color(200, 100, 50);

        $ratio = ColorAnalyzer::getContrastRatio($color1, $color2);

        expect($ratio)->toBeGreaterThanOrEqual(1.0);
        expect($ratio)->toBeLessThanOrEqual(21.0);
    });
});

describe('ColorAnalyzer WCAG AA Compliance', function () {
    test('it validates black text on white background meets AA for normal text', function () {
        $black = new Color(0, 0, 0);
        $white = new Color(255, 255, 255);

        expect(ColorAnalyzer::meetsWCAG_AA($black, $white, false))->toBeTrue();
    });

    test('it validates black text on white background meets AA for large text', function () {
        $black = new Color(0, 0, 0);
        $white = new Color(255, 255, 255);

        expect(ColorAnalyzer::meetsWCAG_AA($black, $white, true))->toBeTrue();
    });

    test('it detects when colors do not meet AA for normal text', function () {
        $lightGray = new Color(170, 170, 170);
        $white = new Color(255, 255, 255);

        // Light gray on white has low contrast
        expect(ColorAnalyzer::meetsWCAG_AA($lightGray, $white, false))->toBeFalse();
    });

    test('it requires 4.5:1 ratio for normal text', function () {
        // Color combo with approximately 4.5:1 ratio
        $color1 = new Color(119, 119, 119);
        $white = new Color(255, 255, 255);

        $ratio = ColorAnalyzer::getContrastRatio($color1, $white);

        if ($ratio >= 4.5) {
            expect(ColorAnalyzer::meetsWCAG_AA($color1, $white, false))->toBeTrue();
        } else {
            expect(ColorAnalyzer::meetsWCAG_AA($color1, $white, false))->toBeFalse();
        }
    });

    test('it requires 3:1 ratio for large text', function () {
        // Color combo with approximately 3:1 ratio
        $color1 = new Color(150, 150, 150);
        $white = new Color(255, 255, 255);

        $ratio = ColorAnalyzer::getContrastRatio($color1, $white);

        if ($ratio >= 3.0) {
            expect(ColorAnalyzer::meetsWCAG_AA($color1, $white, true))->toBeTrue();
        } else {
            expect(ColorAnalyzer::meetsWCAG_AA($color1, $white, true))->toBeFalse();
        }
    });
});

describe('ColorAnalyzer WCAG AAA Compliance', function () {
    test('it validates black text on white background meets AAA for normal text', function () {
        $black = new Color(0, 0, 0);
        $white = new Color(255, 255, 255);

        expect(ColorAnalyzer::meetsWCAG_AAA($black, $white, false))->toBeTrue();
    });

    test('it validates black text on white background meets AAA for large text', function () {
        $black = new Color(0, 0, 0);
        $white = new Color(255, 255, 255);

        expect(ColorAnalyzer::meetsWCAG_AAA($black, $white, true))->toBeTrue();
    });

    test('it has stricter requirements than AA for normal text', function () {
        // Color with ratio between 4.5 and 7 should pass AA but fail AAA
        $color1 = new Color(95, 95, 95);
        $white = new Color(255, 255, 255);

        $ratio = ColorAnalyzer::getContrastRatio($color1, $white);

        // If ratio is between 4.5 and 7
        if ($ratio >= 4.5 && $ratio < 7.0) {
            expect(ColorAnalyzer::meetsWCAG_AA($color1, $white, false))->toBeTrue();
            expect(ColorAnalyzer::meetsWCAG_AAA($color1, $white, false))->toBeFalse();
        }
    });

    test('it requires 7:1 ratio for normal text', function () {
        // Dark gray that should meet 7:1 ratio
        $darkGray = new Color(85, 85, 85);
        $white = new Color(255, 255, 255);

        $ratio = ColorAnalyzer::getContrastRatio($darkGray, $white);

        if ($ratio >= 7.0) {
            expect(ColorAnalyzer::meetsWCAG_AAA($darkGray, $white, false))->toBeTrue();
        } else {
            expect(ColorAnalyzer::meetsWCAG_AAA($darkGray, $white, false))->toBeFalse();
        }
    });

    test('it requires 4.5:1 ratio for large text', function () {
        $color1 = new Color(119, 119, 119);
        $white = new Color(255, 255, 255);

        $ratio = ColorAnalyzer::getContrastRatio($color1, $white);

        if ($ratio >= 4.5) {
            expect(ColorAnalyzer::meetsWCAG_AAA($color1, $white, true))->toBeTrue();
        } else {
            expect(ColorAnalyzer::meetsWCAG_AAA($color1, $white, true))->toBeFalse();
        }
    });
});

describe('ColorAnalyzer RGB Distance', function () {
    test('it calculates zero distance for identical colors', function () {
        $color = new Color(100, 150, 200);

        $distance = ColorAnalyzer::getRgbDistance($color, $color);

        expect($distance)->toBe(0.0);
    });

    test('it calculates maximum distance for black and white', function () {
        $black = new Color(0, 0, 0);
        $white = new Color(255, 255, 255);

        $distance = ColorAnalyzer::getRgbDistance($black, $white);

        // sqrt(255^2 + 255^2 + 255^2) = sqrt(195075) ≈ 441.67
        expect(abs($distance - 441.67))->toBeLessThan(0.1);
    });

    test('it calculates distance symmetrically', function () {
        $color1 = new Color(100, 150, 200);
        $color2 = new Color(200, 100, 50);

        $distance1 = ColorAnalyzer::getRgbDistance($color1, $color2);
        $distance2 = ColorAnalyzer::getRgbDistance($color2, $color1);

        expect($distance1)->toBe($distance2);
    });

    test('it calculates distance using Euclidean formula', function () {
        $color1 = new Color(0, 0, 0);
        $color2 = new Color(3, 4, 0);

        $distance = ColorAnalyzer::getRgbDistance($color1, $color2);

        // sqrt(3^2 + 4^2) = 5
        expect($distance)->toBe(5.0);
    });
});

describe('ColorAnalyzer Delta E (CIE76)', function () {
    test('it calculates zero Delta E for identical colors', function () {
        $color = new Color(100, 150, 200);

        $deltaE = ColorAnalyzer::getDeltaE($color, $color);

        expect($deltaE)->toBeLessThan(0.1);
    });

    test('it calculates Delta E symmetrically', function () {
        $color1 = new Color(255, 0, 0);
        $color2 = new Color(0, 0, 255);

        $deltaE1 = ColorAnalyzer::getDeltaE($color1, $color2);
        $deltaE2 = ColorAnalyzer::getDeltaE($color2, $color1);

        expect(abs($deltaE1 - $deltaE2))->toBeLessThan(0.01);
    });

    test('it calculates higher Delta E for very different colors', function () {
        $black = new Color(0, 0, 0);
        $white = new Color(255, 255, 255);

        $deltaE = ColorAnalyzer::getDeltaE($black, $white);

        expect($deltaE)->toBeGreaterThan(50);
    });

    test('it calculates lower Delta E for similar colors', function () {
        $color1 = new Color(100, 100, 100);
        $color2 = new Color(105, 105, 105);

        $deltaE = ColorAnalyzer::getDeltaE($color1, $color2);

        expect($deltaE)->toBeLessThan(10);
    });

    test('it uses LAB color space for perceptual accuracy', function () {
        $red1 = new Color(255, 0, 0);
        $red2 = new Color(250, 0, 0);

        $deltaE = ColorAnalyzer::getDeltaE($red1, $red2);

        // Should be small but measurable
        expect($deltaE)->toBeGreaterThan(0);
        expect($deltaE)->toBeLessThan(20);
    });
});

describe('ColorAnalyzer Vibrant Colors', function () {
    test('it identifies highly saturated colors as vibrant', function () {
        $vibrant = new Color(255, 0, 0); // Pure red, high saturation

        expect(ColorAnalyzer::isVibrant($vibrant))->toBeTrue();
    });

    test('it does not identify low saturation colors as vibrant', function () {
        $muted = new Color(150, 140, 145); // Low saturation

        expect(ColorAnalyzer::isVibrant($muted))->toBeFalse();
    });

    test('it uses default threshold of 70', function () {
        $color = Color::fromHsl(0, 75, 50); // 75% saturation

        expect(ColorAnalyzer::isVibrant($color))->toBeTrue();
    });

    test('it respects custom threshold', function () {
        $color = Color::fromHsl(0, 60, 50); // 60% saturation

        expect(ColorAnalyzer::isVibrant($color, 50))->toBeTrue();
        expect(ColorAnalyzer::isVibrant($color, 70))->toBeFalse();
    });

    test('it identifies pure primary colors as vibrant', function () {
        $red = new Color(255, 0, 0);
        $green = new Color(0, 255, 0);
        $blue = new Color(0, 0, 255);

        expect(ColorAnalyzer::isVibrant($red))->toBeTrue();
        expect(ColorAnalyzer::isVibrant($green))->toBeTrue();
        expect(ColorAnalyzer::isVibrant($blue))->toBeTrue();
    });
});

describe('ColorAnalyzer Muted Colors', function () {
    test('it identifies low saturation colors as muted', function () {
        $muted = new Color(150, 140, 145); // Low saturation

        expect(ColorAnalyzer::isMuted($muted))->toBeTrue();
    });

    test('it does not identify highly saturated colors as muted', function () {
        $vibrant = new Color(255, 0, 0); // Pure red

        expect(ColorAnalyzer::isMuted($vibrant))->toBeFalse();
    });

    test('it uses default threshold of 30', function () {
        $color = Color::fromHsl(0, 25, 50); // 25% saturation

        expect(ColorAnalyzer::isMuted($color))->toBeTrue();
    });

    test('it respects custom threshold', function () {
        $color = Color::fromHsl(0, 40, 50); // 40% saturation

        expect(ColorAnalyzer::isMuted($color, 50))->toBeTrue();
        expect(ColorAnalyzer::isMuted($color, 30))->toBeFalse();
    });

    test('it identifies grayscale colors as muted', function () {
        $gray1 = new Color(100, 100, 100);
        $gray2 = new Color(200, 200, 200);

        expect(ColorAnalyzer::isMuted($gray1))->toBeTrue();
        expect(ColorAnalyzer::isMuted($gray2))->toBeTrue();
    });
});

describe('ColorAnalyzer Warm Colors', function () {
    test('it identifies red as warm', function () {
        $red = new Color(255, 0, 0); // Hue = 0

        expect(ColorAnalyzer::isWarm($red))->toBeTrue();
    });

    test('it identifies orange as warm', function () {
        $orange = Color::fromHsl(30, 100, 50); // Hue = 30

        expect(ColorAnalyzer::isWarm($orange))->toBeTrue();
    });

    test('it identifies yellow as warm', function () {
        $yellow = Color::fromHsl(60, 100, 50); // Hue = 60

        expect(ColorAnalyzer::isWarm($yellow))->toBeTrue();
    });

    test('it does not identify green as warm', function () {
        $green = new Color(0, 255, 0); // Hue = 120

        expect(ColorAnalyzer::isWarm($green))->toBeFalse();
    });

    test('it does not identify blue as warm', function () {
        $blue = new Color(0, 0, 255); // Hue = 240

        expect(ColorAnalyzer::isWarm($blue))->toBeFalse();
    });

    test('it checks hue range 0-60 degrees', function () {
        $hue0 = Color::fromHsl(0, 100, 50);
        $hue30 = Color::fromHsl(30, 100, 50);
        $hue60 = Color::fromHsl(60, 100, 50);
        $hue61 = Color::fromHsl(61, 100, 50);

        expect(ColorAnalyzer::isWarm($hue0))->toBeTrue();
        expect(ColorAnalyzer::isWarm($hue30))->toBeTrue();
        expect(ColorAnalyzer::isWarm($hue60))->toBeTrue();
        expect(ColorAnalyzer::isWarm($hue61))->toBeFalse();
    });
});

describe('ColorAnalyzer Cool Colors', function () {
    test('it identifies green as cool', function () {
        $green = new Color(0, 255, 0); // Hue = 120

        expect(ColorAnalyzer::isCool($green))->toBeTrue();
    });

    test('it identifies cyan as cool', function () {
        $cyan = Color::fromHsl(180, 100, 50); // Hue = 180

        expect(ColorAnalyzer::isCool($cyan))->toBeTrue();
    });

    test('it identifies blue as cool', function () {
        $blue = new Color(0, 0, 255); // Hue = 240

        expect(ColorAnalyzer::isCool($blue))->toBeTrue();
    });

    test('it identifies purple as cool', function () {
        $purple = Color::fromHsl(270, 100, 50); // Hue = 270

        expect(ColorAnalyzer::isCool($purple))->toBeTrue();
    });

    test('it does not identify red as cool', function () {
        $red = new Color(255, 0, 0); // Hue = 0

        expect(ColorAnalyzer::isCool($red))->toBeFalse();
    });

    test('it does not identify yellow as cool', function () {
        $yellow = Color::fromHsl(60, 100, 50); // Hue = 60

        expect(ColorAnalyzer::isCool($yellow))->toBeFalse();
    });

    test('it checks hue range 120-300 degrees', function () {
        $hue119 = Color::fromHsl(119, 100, 50);
        $hue120 = Color::fromHsl(120, 100, 50);
        $hue210 = Color::fromHsl(210, 100, 50);
        $hue300 = Color::fromHsl(300, 100, 50);
        $hue301 = Color::fromHsl(301, 100, 50);

        expect(ColorAnalyzer::isCool($hue119))->toBeFalse();
        expect(ColorAnalyzer::isCool($hue120))->toBeTrue();
        expect(ColorAnalyzer::isCool($hue210))->toBeTrue();
        expect(ColorAnalyzer::isCool($hue300))->toBeTrue();
        expect(ColorAnalyzer::isCool($hue301))->toBeFalse();
    });
});

describe('ColorAnalyzer Edge Cases', function () {
    test('it handles black color correctly', function () {
        $black = new Color(0, 0, 0);

        expect(ColorAnalyzer::getBrightness($black))->toBe(0.0);
        expect(ColorAnalyzer::getLuminance($black))->toBe(0.0);
        expect(ColorAnalyzer::isDark($black))->toBeTrue();
    });

    test('it handles white color correctly', function () {
        $white = new Color(255, 255, 255);

        expect(ColorAnalyzer::getBrightness($white))->toBe(255.0);
        expect(abs(ColorAnalyzer::getLuminance($white) - 1.0))->toBeLessThan(0.01);
        expect(ColorAnalyzer::isLight($white))->toBeTrue();
    });

    test('it handles grayscale colors', function () {
        $gray = new Color(128, 128, 128);

        // Grayscale should be muted (no saturation)
        expect(ColorAnalyzer::isMuted($gray))->toBeTrue();
        expect(ColorAnalyzer::isVibrant($gray))->toBeFalse();
    });
});

describe('ColorAnalyzer Consistency', function () {
    test('it produces consistent brightness results', function () {
        $color = new Color(123, 45, 67);

        $result1 = ColorAnalyzer::getBrightness($color);
        $result2 = ColorAnalyzer::getBrightness($color);

        expect($result1)->toBe($result2);
    });

    test('it produces consistent contrast ratios', function () {
        $color1 = new Color(100, 150, 200);
        $color2 = new Color(50, 75, 100);

        $ratio1 = ColorAnalyzer::getContrastRatio($color1, $color2);
        $ratio2 = ColorAnalyzer::getContrastRatio($color1, $color2);

        expect($ratio1)->toBe($ratio2);
    });

    test('it produces consistent Delta E values', function () {
        $color1 = new Color(100, 150, 200);
        $color2 = new Color(110, 160, 210);

        $deltaE1 = ColorAnalyzer::getDeltaE($color1, $color2);
        $deltaE2 = ColorAnalyzer::getDeltaE($color1, $color2);

        expect($deltaE1)->toBe($deltaE2);
    });
});
