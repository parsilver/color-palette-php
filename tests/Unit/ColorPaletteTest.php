<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;

describe('ColorPalette Basic Operations', function () {
    test('it can get colors from palette', function () {
        $colors = [
            new Color(255, 0, 0),
            new Color(0, 255, 0),
            new Color(0, 0, 255),
        ];

        $palette = new ColorPalette($colors);

        expect($palette->getColors())->toBe($colors);
        expect($palette->getColors())->toHaveCount(3);
    });

    test('it can convert palette to array of hex colors', function () {
        $palette = new ColorPalette([
            new Color(255, 0, 0),
            new Color(0, 255, 0),
            new Color(0, 0, 255),
        ]);

        $hexArray = $palette->toArray();

        expect($hexArray)->toBe(['#ff0000', '#00ff00', '#0000ff']);
    });

    test('it preserves array keys when converting to hex array', function () {
        $palette = new ColorPalette([
            'red' => new Color(255, 0, 0),
            'green' => new Color(0, 255, 0),
            'blue' => new Color(0, 0, 255),
        ]);

        $hexArray = $palette->toArray();

        expect($hexArray)->toBe([
            'red' => '#ff0000',
            'green' => '#00ff00',
            'blue' => '#0000ff',
        ]);
    });

    test('it can count colors in palette', function () {
        $palette = new ColorPalette([
            new Color(255, 0, 0),
            new Color(0, 255, 0),
        ]);

        expect($palette->count())->toBe(2);
        expect(count($palette))->toBe(2);
    });

    test('it handles empty palette', function () {
        $palette = new ColorPalette([]);

        expect($palette->count())->toBe(0);
        expect($palette->getColors())->toBeEmpty();
        expect($palette->toArray())->toBeEmpty();
    });
});

describe('ColorPalette ArrayAccess Implementation', function () {
    test('it can check if offset exists', function () {
        $palette = new ColorPalette([
            new Color(255, 0, 0),
            new Color(0, 255, 0),
        ]);

        expect(isset($palette[0]))->toBeTrue();
        expect(isset($palette[1]))->toBeTrue();
        expect(isset($palette[2]))->toBeFalse();
    });

    test('it can check if named offset exists', function () {
        $palette = new ColorPalette([
            'primary' => new Color(255, 0, 0),
            'secondary' => new Color(0, 255, 0),
        ]);

        expect(isset($palette['primary']))->toBeTrue();
        expect(isset($palette['secondary']))->toBeTrue();
        expect(isset($palette['accent']))->toBeFalse();
    });

    test('it can get color by offset', function () {
        $red = new Color(255, 0, 0);
        $green = new Color(0, 255, 0);
        $palette = new ColorPalette([$red, $green]);

        expect($palette[0])->toBe($red);
        expect($palette[1])->toBe($green);
    });

    test('it throws exception for non-existent offset', function () {
        $palette = new ColorPalette([new Color(255, 0, 0)]);

        expect(fn () => $palette[10])->toThrow(\OutOfBoundsException::class);
        expect(fn () => $palette['non-existent'])->toThrow(\OutOfBoundsException::class);
    });

    test('it can set color at numeric offset', function () {
        $palette = new ColorPalette([new Color(255, 0, 0)]);
        $newColor = new Color(0, 255, 0);

        $palette[1] = $newColor;

        expect($palette[1])->toBe($newColor);
        expect($palette->count())->toBe(2);
    });

    test('it can set color at named offset', function () {
        $palette = new ColorPalette([]);
        $color = new Color(255, 0, 0);

        $palette['primary'] = $color;

        expect($palette['primary'])->toBe($color);
        expect($palette->count())->toBe(1);
    });

    test('it can append color with null offset', function () {
        $palette = new ColorPalette([new Color(255, 0, 0)]);
        $newColor = new Color(0, 255, 0);

        $palette[] = $newColor;

        expect($palette[1])->toBe($newColor);
        expect($palette->count())->toBe(2);
    });

    test('it throws exception when setting non-ColorInterface value', function () {
        $palette = new ColorPalette([]);

        expect(fn () => $palette[0] = 'not a color')
            ->toThrow(InvalidArgumentException::class, 'Value must be an instance of ColorInterface');
    });

    test('it can unset color at offset', function () {
        $palette = new ColorPalette([
            new Color(255, 0, 0),
            new Color(0, 255, 0),
        ]);

        unset($palette[0]);

        expect(isset($palette[0]))->toBeFalse();
        expect($palette->count())->toBe(1);
    });

    test('it can replace existing color', function () {
        $palette = new ColorPalette([
            'primary' => new Color(255, 0, 0),
        ]);
        $newColor = new Color(0, 0, 255);

        $palette['primary'] = $newColor;

        expect($palette['primary'])->toBe($newColor);
        expect($palette->count())->toBe(1);
    });
});

describe('ColorPalette Text Color Suggestions', function () {
    test('it suggests white text for dark background', function () {
        $darkBackground = new Color(0, 0, 0); // Black
        $palette = new ColorPalette([$darkBackground]);

        $textColor = $palette->getSuggestedTextColor($darkBackground);

        expect($textColor->toHex())->toBe('#ffffff'); // White
    });

    test('it suggests black text for light background', function () {
        $lightBackground = new Color(255, 255, 255); // White
        $palette = new ColorPalette([$lightBackground]);

        $textColor = $palette->getSuggestedTextColor($lightBackground);

        expect($textColor->toHex())->toBe('#000000'); // Black
    });

    test('it suggests appropriate text color based on contrast ratio', function () {
        $mediumBackground = new Color(128, 128, 128); // Gray
        $palette = new ColorPalette([$mediumBackground]);

        $textColor = $palette->getSuggestedTextColor($mediumBackground);

        // Should be either white or black, whichever has better contrast
        expect($textColor->toHex())->toBeIn(['#ffffff', '#000000']);
    });

    test('it suggests white text for blue background', function () {
        $blueBackground = new Color(0, 0, 255);
        $palette = new ColorPalette([$blueBackground]);

        $textColor = $palette->getSuggestedTextColor($blueBackground);

        expect($textColor->toHex())->toBe('#ffffff');
    });

    test('it suggests black text for yellow background', function () {
        $yellowBackground = new Color(255, 255, 0);
        $palette = new ColorPalette([$yellowBackground]);

        $textColor = $palette->getSuggestedTextColor($yellowBackground);

        expect($textColor->toHex())->toBe('#000000');
    });
});

describe('ColorPalette Surface Color Suggestions', function () {
    test('it returns empty array for empty palette', function () {
        $palette = new ColorPalette([]);

        $surfaceColors = $palette->getSuggestedSurfaceColors();

        expect($surfaceColors)->toBeEmpty();
    });

    test('it generates surface colors for single color palette', function () {
        $palette = new ColorPalette([new Color(100, 150, 200)]);

        $surfaceColors = $palette->getSuggestedSurfaceColors();

        expect($surfaceColors)->toHaveKey('surface');
        expect($surfaceColors)->toHaveKey('background');
        expect($surfaceColors)->toHaveKey('accent');
        expect($surfaceColors)->toHaveKey('surface_variant');
        expect($surfaceColors['surface'])->toBeInstanceOf(\Farzai\ColorPalette\Contracts\ColorInterface::class);
    });

    test('it generates surface colors sorted by brightness', function () {
        $palette = new ColorPalette([
            new Color(50, 50, 50),   // Dark
            new Color(200, 200, 200), // Light
            new Color(100, 100, 100), // Medium
        ]);

        $surfaceColors = $palette->getSuggestedSurfaceColors();

        // Surface should be the lightest color
        expect($surfaceColors['surface']->toHex())->toBe('#c8c8c8');
    });

    test('it uses same color for surface and background when only one color available', function () {
        $palette = new ColorPalette([new Color(150, 150, 150)]);

        $surfaceColors = $palette->getSuggestedSurfaceColors();

        expect($surfaceColors['surface']->toHex())->toBe($surfaceColors['background']->toHex());
    });

    test('it generates different surface and background colors for multiple colors', function () {
        $palette = new ColorPalette([
            new Color(100, 100, 100),
            new Color(200, 200, 200),
            new Color(150, 150, 150),
        ]);

        $surfaceColors = $palette->getSuggestedSurfaceColors();

        expect($surfaceColors['surface']->toHex())->not->toBe($surfaceColors['background']->toHex());
    });

    test('it generates accent color with good contrast', function () {
        $palette = new ColorPalette([
            new Color(255, 255, 255), // White
            new Color(128, 128, 128), // Gray - good contrast with both
            new Color(0, 0, 0),       // Black
        ]);

        $surfaceColors = $palette->getSuggestedSurfaceColors();

        expect($surfaceColors['accent'])->toBeInstanceOf(\Farzai\ColorPalette\Contracts\ColorInterface::class);
    });

    test('it generates surface variant based on surface color lightness', function () {
        $lightPalette = new ColorPalette([new Color(240, 240, 240)]);

        $surfaceColors = $lightPalette->getSuggestedSurfaceColors();

        // For light color, variant should be darker
        expect($surfaceColors['surface_variant'])->toBeInstanceOf(\Farzai\ColorPalette\Contracts\ColorInterface::class);
        expect($surfaceColors['surface_variant']->toHex())->not->toBe($surfaceColors['surface']->toHex());
    });

    test('it generates darker variant for light surfaces', function () {
        $palette = new ColorPalette([new Color(255, 255, 255)]); // White

        $surfaceColors = $palette->getSuggestedSurfaceColors();
        $surface = $surfaceColors['surface'];
        $variant = $surfaceColors['surface_variant'];

        expect($variant->getBrightness())->toBeLessThan($surface->getBrightness());
    });

    test('it generates lighter variant for dark surfaces', function () {
        $palette = new ColorPalette([new Color(50, 50, 50)]); // Dark gray

        $surfaceColors = $palette->getSuggestedSurfaceColors();
        $surface = $surfaceColors['surface'];
        $variant = $surfaceColors['surface_variant'];

        expect($variant->getBrightness())->toBeGreaterThan($surface->getBrightness());
    });
});
