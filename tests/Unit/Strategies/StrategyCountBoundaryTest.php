<?php

declare(strict_types=1);

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\MonochromaticStrategy;
use Farzai\ColorPalette\Strategies\ShadesStrategy;
use Farzai\ColorPalette\Strategies\TintsStrategy;

/**
 * Regression: count-based strategies computed `step = X / (count - 1)` before the
 * loop, so count=1 (a value MIN_COUNT=1 explicitly allows) threw DivisionByZeroError.
 */
describe('Count-based strategies handle count=1 without dividing by zero', function () {
    $strategies = [
        'monochromatic' => fn () => new MonochromaticStrategy,
        'shades' => fn () => new ShadesStrategy,
        'tints' => fn () => new TintsStrategy,
    ];

    foreach ($strategies as $name => $make) {
        test("{$name} returns a single-color palette of the base color when count=1", function () use ($make) {
            $strategy = $make();
            $baseColor = new Color(120, 80, 40);

            $palette = $strategy->generate($baseColor, ['count' => 1]);

            expect($palette)->toBeInstanceOf(ColorPalette::class);
            expect($palette->count())->toBe(1);
            expect($palette->getColors()[0])->toBe($baseColor);
        });
    }
});
