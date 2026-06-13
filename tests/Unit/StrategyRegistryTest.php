<?php

declare(strict_types=1);

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;
use Farzai\ColorPalette\Strategies\SplitComplementaryStrategy;
use Farzai\ColorPalette\Strategies\WebsiteThemeStrategy;
use Farzai\ColorPalette\StrategyRegistry;

describe('StrategyRegistry', function () {
    test('it resolves every canonical scheme name to a strategy', function () {
        foreach (StrategyRegistry::names() as $name) {
            expect(StrategyRegistry::resolve($name))
                ->toBeInstanceOf(PaletteGenerationStrategyInterface::class);
        }
    });

    test('it treats spelling variants as the same scheme', function () {
        foreach (['split-complementary', 'splitComplementary', 'splitcomplementary', 'SPLIT_COMPLEMENTARY'] as $alias) {
            expect(StrategyRegistry::resolve($alias))->toBeInstanceOf(SplitComplementaryStrategy::class);
        }

        foreach (['website-theme', 'websiteTheme', 'websitetheme'] as $alias) {
            expect(StrategyRegistry::resolve($alias))->toBeInstanceOf(WebsiteThemeStrategy::class);
        }
    });

    test('has() reports membership regardless of spelling', function () {
        expect(StrategyRegistry::has('splitComplementary'))->toBeTrue();
        expect(StrategyRegistry::has('TRIADIC'))->toBeTrue();
        expect(StrategyRegistry::has('does-not-exist'))->toBeFalse();
    });

    test('it throws a clear error for unknown schemes', function () {
        expect(fn () => StrategyRegistry::resolve('nope'))
            ->toThrow(InvalidArgumentException::class, 'Unknown color scheme: nope');
    });

    test('ColorPalette::fromColor now accepts the camelCase alias the registry unified', function () {
        $palette = ColorPalette::fromColor(new Color(52, 152, 219), 'splitcomplementary');

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBeGreaterThan(0);
    });
});
