<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\ColorPaletteBuilder;
use Farzai\ColorPalette\Strategies\MonochromaticStrategy;

describe('ColorPaletteBuilder Basic Operations', function () {
    test('it can create a builder instance', function () {
        $builder = ColorPaletteBuilder::create();

        expect($builder)->toBeInstanceOf(ColorPaletteBuilder::class);
    });

    test('it can add a single color', function () {
        $color = new Color(255, 0, 0);
        $palette = ColorPaletteBuilder::create()
            ->addColor($color)
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(1);
        expect($palette->getColors()[0])->toBe($color);
    });

    test('it can add a single color with string key', function () {
        $color = new Color(255, 0, 0);
        $palette = ColorPaletteBuilder::create()
            ->addColor($color, 'red')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(1);
        expect($palette->getColors()['red'])->toBe($color);
    });

    test('it can add a single color with integer key', function () {
        $color = new Color(255, 0, 0);
        $palette = ColorPaletteBuilder::create()
            ->addColor($color, 0)
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(1);
        expect($palette->getColors()[0])->toBe($color);
    });

    test('it can add multiple colors', function () {
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

    test('it can add multiple colors at once', function () {
        $colors = [
            new Color(255, 0, 0),
            new Color(0, 255, 0),
            new Color(0, 0, 255),
        ];

        $palette = ColorPaletteBuilder::create()
            ->addColors($colors)
            ->build();

        expect($palette->count())->toBe(3);
        expect($palette->getColors())->toBe($colors);
    });

    test('it can add multiple colors with named keys', function () {
        $colors = [
            'red' => new Color(255, 0, 0),
            'green' => new Color(0, 255, 0),
            'blue' => new Color(0, 0, 255),
        ];

        $palette = ColorPaletteBuilder::create()
            ->addColors($colors)
            ->build();

        expect($palette->count())->toBe(3);
        expect($palette->getColors()['red'])->toBe($colors['red']);
        expect($palette->getColors()['green'])->toBe($colors['green']);
        expect($palette->getColors()['blue'])->toBe($colors['blue']);
    });

    test('it can chain multiple operations', function () {
        $palette = ColorPaletteBuilder::create()
            ->addColor(new Color(255, 0, 0))
            ->addColor(new Color(0, 255, 0))
            ->addColor(new Color(0, 0, 255))
            ->build();

        expect($palette->count())->toBe(3);
    });

    test('it returns empty palette when no colors are added', function () {
        $palette = ColorPaletteBuilder::create()->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(0);
    });
});

describe('ColorPaletteBuilder Base Color', function () {
    test('it can set base color', function () {
        $baseColor = new Color(255, 0, 0);
        $builder = ColorPaletteBuilder::create()
            ->withBaseColor($baseColor);

        expect($builder)->toBeInstanceOf(ColorPaletteBuilder::class);
    });

    test('it returns fluent interface from withBaseColor', function () {
        $baseColor = new Color(255, 0, 0);
        $builder = ColorPaletteBuilder::create();
        $result = $builder->withBaseColor($baseColor);

        expect($result)->toBe($builder);
    });
});

describe('ColorPaletteBuilder Strategy Configuration', function () {
    test('it can set strategy by name', function () {
        $builder = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('monochromatic');

        expect($builder)->toBeInstanceOf(ColorPaletteBuilder::class);
    });

    test('it can set strategy with options', function () {
        $builder = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('monochromatic', ['count' => 7]);

        expect($builder)->toBeInstanceOf(ColorPaletteBuilder::class);
    });

    test('it can set strategy instance directly', function () {
        $strategy = new MonochromaticStrategy;
        $builder = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme($strategy);

        expect($builder)->toBeInstanceOf(ColorPaletteBuilder::class);
    });

    test('it returns fluent interface from withScheme', function () {
        $builder = ColorPaletteBuilder::create();
        $result = $builder->withScheme('monochromatic');

        expect($result)->toBe($builder);
    });

    test('it throws exception for unknown strategy name', function () {
        ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('unknown-strategy')
            ->build();
    })->throws(InvalidArgumentException::class, 'Unknown color scheme: unknown-strategy');
});

describe('ColorPaletteBuilder Strategy Name Resolution', function () {
    test('it resolves monochromatic strategy', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('monochromatic', ['count' => 5])
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it resolves complementary strategy', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('complementary')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves analogous strategy', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('analogous')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves triadic strategy', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('triadic')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves tetradic strategy', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('tetradic')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves split-complementary strategy with hyphen', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('split-complementary')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves split-complementary strategy without hyphen', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('splitcomplementary')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves shades strategy', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('shades')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves tints strategy', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('tints')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves pastel strategy', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('pastel')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves vibrant strategy', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('vibrant')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves website-theme strategy with hyphen', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('website-theme')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it resolves website-theme strategy without hyphen', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('websitetheme')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
    });

    test('it is case insensitive for strategy names', function () {
        $palette1 = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('MONOCHROMATIC')
            ->build();

        $palette2 = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('Monochromatic')
            ->build();

        $palette3 = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('monochromatic')
            ->build();

        expect($palette1)->toBeInstanceOf(ColorPalette::class);
        expect($palette2)->toBeInstanceOf(ColorPalette::class);
        expect($palette3)->toBeInstanceOf(ColorPalette::class);
    });
});

describe('ColorPaletteBuilder Image Extraction', function () {
    test('it can set image path', function () {
        $builder = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg');

        expect($builder)->toBeInstanceOf(ColorPaletteBuilder::class);
    });

    test('it can set extraction count', function () {
        $builder = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(10);

        expect($builder)->toBeInstanceOf(ColorPaletteBuilder::class);
    });

    test('it returns fluent interface from fromImage', function () {
        $builder = ColorPaletteBuilder::create();
        $result = $builder->fromImage(__DIR__.'/../../example/assets/sample.jpg');

        expect($result)->toBe($builder);
    });

    test('it returns fluent interface from withCount', function () {
        $builder = ColorPaletteBuilder::create();
        $result = $builder->withCount(10);

        expect($result)->toBe($builder);
    });

    test('it can build palette from image', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $palette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(5)
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it defaults to 5 colors when count not specified', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $palette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });
});

describe('ColorPaletteBuilder Priority Handling', function () {
    test('it prioritizes manual colors over image extraction', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $manualColor = new Color(255, 0, 0);
        $palette = ColorPaletteBuilder::create()
            ->addColor($manualColor)
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->build();

        expect($palette->count())->toBe(1);
        expect($palette->getColors()[0])->toBe($manualColor);
    });

    test('it prioritizes manual colors over strategy', function () {
        $manualColor = new Color(255, 0, 0);
        $palette = ColorPaletteBuilder::create()
            ->addColor($manualColor)
            ->withBaseColor(new Color(0, 255, 0))
            ->withScheme('monochromatic')
            ->build();

        expect($palette->count())->toBe(1);
        expect($palette->getColors()[0])->toBe($manualColor);
    });

    test('it prioritizes image extraction over strategy', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $palette = ColorPaletteBuilder::create()
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(3)
            ->withBaseColor(new Color(0, 255, 0))
            ->withScheme('monochromatic', ['count' => 10])
            ->build();

        // Should use image extraction, not strategy
        expect($palette->count())->toBe(3);
    });

    test('it uses strategy when no manual colors or image specified', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->withScheme('monochromatic', ['count' => 7])
            ->build();

        expect($palette->count())->toBe(7);
    });

    test('it returns empty palette when nothing is specified', function () {
        $palette = ColorPaletteBuilder::create()->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(0);
    });

    test('it requires base color for strategy generation', function () {
        // Strategy without base color should return empty palette
        $palette = ColorPaletteBuilder::create()
            ->withScheme('monochromatic')
            ->build();

        expect($palette->count())->toBe(0);
    });

    test('it requires strategy for strategy generation', function () {
        // Base color without strategy should return empty palette
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(255, 0, 0))
            ->build();

        expect($palette->count())->toBe(0);
    });
});

describe('ColorPaletteBuilder Complex Workflows', function () {
    test('it can build palette with all configuration options', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $builder = ColorPaletteBuilder::create()
            ->addColor(new Color(255, 0, 0))
            ->withBaseColor(new Color(0, 255, 0))
            ->withScheme('monochromatic', ['count' => 5])
            ->fromImage(__DIR__.'/../../example/assets/sample.jpg')
            ->withCount(10);

        expect($builder)->toBeInstanceOf(ColorPaletteBuilder::class);

        // Manual colors take priority
        $palette = $builder->build();
        expect($palette->count())->toBe(1);
    });

    test('it can create multiple palettes from same builder configuration', function () {
        $builder = ColorPaletteBuilder::create()
            ->addColor(new Color(255, 0, 0))
            ->addColor(new Color(0, 255, 0));

        $palette1 = $builder->build();
        $palette2 = $builder->build();

        expect($palette1)->toBeInstanceOf(ColorPalette::class);
        expect($palette2)->toBeInstanceOf(ColorPalette::class);
        expect($palette1->count())->toBe(2);
        expect($palette2->count())->toBe(2);
    });

    test('it can build strategy-based palette with custom options', function () {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor(new Color(180, 100, 150))
            ->withScheme('monochromatic', ['count' => 10])
            ->build();

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(10);
    });
});
