<?php

declare(strict_types=1);

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorAnalyzer;
use Farzai\ColorPalette\ColorManipulator;

/**
 * Color's analysis/manipulation methods must delegate to the single-source
 * services rather than re-implementing the formulas. These tests lock that
 * contract so the implementations can never silently diverge again.
 */
describe('Color delegates analysis to ColorAnalyzer', function () {
    $samples = [[0, 0, 0], [255, 255, 255], [18, 52, 86], [200, 100, 50], [120, 200, 60]];

    foreach ($samples as $rgb) {
        [$r, $g, $b] = $rgb;

        test("analysis methods match ColorAnalyzer for rgb({$r},{$g},{$b})", function () use ($r, $g, $b) {
            $color = new Color($r, $g, $b);

            expect($color->getBrightness())->toBe(ColorAnalyzer::getBrightness($color));
            expect($color->getLuminance())->toBe(ColorAnalyzer::getLuminance($color));
            expect($color->isLight())->toBe(ColorAnalyzer::isLight($color));
            expect($color->isDark())->toBe(ColorAnalyzer::isDark($color));

            $other = new Color(255, 255, 255);
            expect($color->getContrastRatio($other))->toBe(ColorAnalyzer::getContrastRatio($color, $other));
        });
    }
});

describe('Color delegates manipulation to ColorManipulator', function () {
    $samples = [[200, 100, 50], [18, 52, 86], [120, 200, 60]];

    foreach ($samples as $rgb) {
        [$r, $g, $b] = $rgb;

        test("manipulation methods match ColorManipulator for rgb({$r},{$g},{$b})", function () use ($r, $g, $b) {
            $color = new Color($r, $g, $b);

            expect($color->lighten(0.2)->toHex())->toBe(ColorManipulator::lighten($color, 0.2)->toHex());
            expect($color->darken(0.2)->toHex())->toBe(ColorManipulator::darken($color, 0.2)->toHex());
            expect($color->saturate(0.2)->toHex())->toBe(ColorManipulator::saturate($color, 0.2)->toHex());
            expect($color->desaturate(0.2)->toHex())->toBe(ColorManipulator::desaturate($color, 0.2)->toHex());
            expect($color->rotate(45)->toHex())->toBe(ColorManipulator::rotate($color, 45)->toHex());
            expect($color->withLightness(0.7)->toHex())->toBe(ColorManipulator::withLightness($color, 0.7)->toHex());
        });
    }
});
