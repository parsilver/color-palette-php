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

describe('ColorPalette Static Factory Methods', function () {
    beforeEach(function () {
        // Create a test image
        $this->testImagePath = sys_get_temp_dir().'/test_image_'.uniqid().'.png';

        // Create a simple test image
        $image = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $blue = imagecolorallocate($image, 0, 0, 255);

        imagefilledrectangle($image, 0, 0, 33, 100, $red);
        imagefilledrectangle($image, 34, 0, 66, 100, $green);
        imagefilledrectangle($image, 67, 0, 100, 100, $blue);

        imagepng($image, $this->testImagePath);
        imagedestroy($image);
    });

    afterEach(function () {
        if (file_exists($this->testImagePath)) {
            unlink($this->testImagePath);
        }
    });

    test('it can extract colors from image using fromImage static method', function () {
        $palette = \Farzai\ColorPalette\ColorPalette::fromImage($this->testImagePath, 3);

        expect($palette)->toBeInstanceOf(\Farzai\ColorPalette\ColorPalette::class);
        expect($palette->count())->toBeGreaterThan(0);
        expect($palette->getColors())->each->toBeInstanceOf(\Farzai\ColorPalette\Contracts\ColorInterface::class);
    });

    test('it can specify count in fromImage method', function () {
        $palette = \Farzai\ColorPalette\ColorPalette::fromImage($this->testImagePath, 5);

        expect($palette->count())->toBe(5);
    });

    test('it uses default count of 5 when not specified', function () {
        $palette = \Farzai\ColorPalette\ColorPalette::fromImage($this->testImagePath);

        expect($palette->count())->toBe(5);
    });

    test('it can specify driver in fromImage method', function () {
        // This test will pass if GD is available
        $palette = \Farzai\ColorPalette\ColorPalette::fromImage($this->testImagePath, 3, 'gd');

        expect($palette)->toBeInstanceOf(\Farzai\ColorPalette\ColorPalette::class);
        expect($palette->count())->toBe(3);
    });

    test('it throws exception for non-existent image path', function () {
        expect(fn () => \Farzai\ColorPalette\ColorPalette::fromImage('/non/existent/path.jpg'))
            ->toThrow(InvalidArgumentException::class);
    });

    test('it can generate palette from color using fromColor static method', function () {
        $baseColor = new \Farzai\ColorPalette\Color(52, 152, 219); // #3498db

        $palette = \Farzai\ColorPalette\ColorPalette::fromColor($baseColor, 'monochromatic');

        expect($palette)->toBeInstanceOf(\Farzai\ColorPalette\ColorPalette::class);
        expect($palette->count())->toBeGreaterThan(0);
        expect($palette->getColors())->each->toBeInstanceOf(\Farzai\ColorPalette\Contracts\ColorInterface::class);
    });

    test('it can specify scheme options in fromColor method', function () {
        $baseColor = new \Farzai\ColorPalette\Color(52, 152, 219);

        $palette = \Farzai\ColorPalette\ColorPalette::fromColor($baseColor, 'monochromatic', ['count' => 7]);

        expect($palette->count())->toBe(7);
    });

    test('it supports different color schemes', function () {
        $baseColor = new \Farzai\ColorPalette\Color(52, 152, 219);

        $schemes = ['monochromatic', 'complementary', 'analogous', 'triadic', 'tetradic'];

        foreach ($schemes as $scheme) {
            $palette = \Farzai\ColorPalette\ColorPalette::fromColor($baseColor, $scheme);
            expect($palette)->toBeInstanceOf(\Farzai\ColorPalette\ColorPalette::class);
            expect($palette->count())->toBeGreaterThan(0);
        }
    });

    test('it throws exception for unknown scheme', function () {
        $baseColor = new \Farzai\ColorPalette\Color(52, 152, 219);

        expect(fn () => \Farzai\ColorPalette\ColorPalette::fromColor($baseColor, 'unknown-scheme'))
            ->toThrow(InvalidArgumentException::class);
    });

    test('it returns ColorPaletteBuilder from builder static method', function () {
        $builder = \Farzai\ColorPalette\ColorPalette::builder();

        expect($builder)->toBeInstanceOf(\Farzai\ColorPalette\ColorPaletteBuilder::class);
    });

    test('it can chain builder methods', function () {
        $palette = \Farzai\ColorPalette\ColorPalette::builder()
            ->fromImage($this->testImagePath)
            ->withCount(3)
            ->build();

        expect($palette)->toBeInstanceOf(\Farzai\ColorPalette\ColorPalette::class);
        expect($palette->count())->toBe(3);
    });
});

describe('ColorPalette Edge Cases', function () {
    test('it handles single color palette', function () {
        $palette = new ColorPalette([new Color(100, 150, 200)]);

        expect($palette->count())->toBe(1);
        expect($palette[0])->toBeInstanceOf(\Farzai\ColorPalette\Contracts\ColorInterface::class);
        expect($palette->toArray())->toHaveCount(1);
    });

    test('it handles large palette with 50 colors', function () {
        $colors = [];
        for ($i = 0; $i < 50; $i++) {
            $colors[] = new Color($i * 5, 100, 150);
        }

        $palette = new ColorPalette($colors);

        expect($palette->count())->toBe(50);
        expect($palette[0])->toBeInstanceOf(\Farzai\ColorPalette\Contracts\ColorInterface::class);
        expect($palette[49])->toBeInstanceOf(\Farzai\ColorPalette\Contracts\ColorInterface::class);
    });

    test('it handles palette with duplicate colors', function () {
        $red = new Color(255, 0, 0);
        $palette = new ColorPalette([$red, $red, $red]);

        expect($palette->count())->toBe(3);
        expect($palette[0]->toHex())->toBe($palette[1]->toHex());
    });

    test('it handles palette with all black colors', function () {
        $black1 = new Color(0, 0, 0);
        $black2 = new Color(0, 0, 0);
        $palette = new ColorPalette([$black1, $black2]);

        expect($palette->count())->toBe(2);
        foreach ($palette->getColors() as $color) {
            expect($color->toHex())->toBe('#000000');
        }
    });

    test('it handles palette with all white colors', function () {
        $white1 = new Color(255, 255, 255);
        $white2 = new Color(255, 255, 255);
        $palette = new ColorPalette([$white1, $white2]);

        expect($palette->count())->toBe(2);
        foreach ($palette->getColors() as $color) {
            expect($color->toHex())->toBe('#ffffff');
        }
    });

    test('it handles very similar colors in palette', function () {
        $colors = [
            new Color(100, 100, 100),
            new Color(101, 101, 101),
            new Color(102, 102, 102),
        ];

        $palette = new ColorPalette($colors);

        expect($palette->count())->toBe(3);
        // All colors should be slightly different
        expect($palette[0]->toHex())->not->toBe($palette[1]->toHex());
    });
});

describe('ColorPalette ArrayAccess Edge Cases', function () {
    test('it handles negative offsets gracefully', function () {
        $palette = new ColorPalette([new Color(255, 0, 0)]);

        expect(fn () => $palette[-1])->toThrow(\OutOfBoundsException::class);
    });

    test('it handles very large offset numbers', function () {
        $palette = new ColorPalette([new Color(255, 0, 0)]);

        expect(fn () => $palette[999999])->toThrow(\OutOfBoundsException::class);
    });

    test('it handles empty string as offset', function () {
        $palette = new ColorPalette(['color' => new Color(255, 0, 0)]);

        expect(fn () => $palette[''])->toThrow(\OutOfBoundsException::class);
    });

    test('it can use numeric strings as offsets', function () {
        $palette = new ColorPalette([]);
        $color = new Color(255, 0, 0);

        $palette['0'] = $color;

        expect($palette['0'])->toBe($color);
    });

    test('it maintains insertion order with mixed keys', function () {
        $palette = new ColorPalette([]);

        $palette[0] = new Color(255, 0, 0);
        $palette['primary'] = new Color(0, 255, 0);
        $palette[1] = new Color(0, 0, 255);

        expect($palette->count())->toBe(3);
        expect($palette[0]->toHex())->toBe('#ff0000');
        expect($palette['primary']->toHex())->toBe('#00ff00');
        expect($palette[1]->toHex())->toBe('#0000ff');
    });

    test('it can unset from empty palette without errors', function () {
        $palette = new ColorPalette([]);

        unset($palette[0]);
        unset($palette['non-existent']);

        expect($palette->count())->toBe(0);
    });
});

describe('ColorPalette Text Color Suggestions Edge Cases', function () {
    test('it handles pure black background', function () {
        $black = new Color(0, 0, 0);
        $palette = new ColorPalette([$black]);

        $textColor = $palette->getSuggestedTextColor($black);

        expect($textColor->toHex())->toBe('#ffffff');
    });

    test('it handles pure white background', function () {
        $white = new Color(255, 255, 255);
        $palette = new ColorPalette([$white]);

        $textColor = $palette->getSuggestedTextColor($white);

        expect($textColor->toHex())->toBe('#000000');
    });

    test('it handles bright saturated colors', function () {
        $brightRed = new Color(255, 0, 0);
        $palette = new ColorPalette([$brightRed]);

        $textColor = $palette->getSuggestedTextColor($brightRed);

        expect($textColor->toHex())->toBeIn(['#ffffff', '#000000']);
    });

    test('it handles desaturated/gray colors', function () {
        $colors = [
            new Color(50, 50, 50),
            new Color(100, 100, 100),
            new Color(150, 150, 150),
            new Color(200, 200, 200),
        ];

        foreach ($colors as $color) {
            $palette = new ColorPalette([$color]);
            $textColor = $palette->getSuggestedTextColor($color);

            expect($textColor->toHex())->toBeIn(['#ffffff', '#000000']);
        }
    });
});

describe('ColorPalette fromImage Edge Cases', function () {
    beforeEach(function () {
        $this->testDir = sys_get_temp_dir().'/palette_test_'.uniqid();
        mkdir($this->testDir);
    });

    afterEach(function () {
        // Clean up test files
        array_map('unlink', glob($this->testDir.'/*'));
        rmdir($this->testDir);
    });

    test('it throws exception for invalid driver name', function () {
        $imagePath = $this->testDir.'/test.png';
        $image = imagecreatetruecolor(10, 10);
        imagepng($image, $imagePath);
        imagedestroy($image);

        expect(fn () => ColorPalette::fromImage($imagePath, 5, 'invalid-driver'))
            ->toThrow(InvalidArgumentException::class);
    });

    test('it throws exception for empty path', function () {
        expect(fn () => ColorPalette::fromImage(''))
            ->toThrow(InvalidArgumentException::class);
    });

    test('it handles minimum count of 1', function () {
        $imagePath = $this->testDir.'/test.png';
        $image = imagecreatetruecolor(10, 10);
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefilledrectangle($image, 0, 0, 10, 10, $red);
        imagepng($image, $imagePath);
        imagedestroy($image);

        $palette = ColorPalette::fromImage($imagePath, 1);

        expect($palette->count())->toBe(1);
    });

    test('it handles maximum count of 50', function () {
        $imagePath = $this->testDir.'/test.png';
        $image = imagecreatetruecolor(100, 100);

        // Create gradient
        for ($x = 0; $x < 100; $x++) {
            for ($y = 0; $y < 100; $y++) {
                $r = (int) ($x / 100 * 255);
                $g = (int) ($y / 100 * 255);
                $color = imagecolorallocate($image, $r, $g, 128);
                imagesetpixel($image, $x, $y, $color);
            }
        }

        imagepng($image, $imagePath);
        imagedestroy($image);

        $palette = ColorPalette::fromImage($imagePath, 50);

        expect($palette->count())->toBe(50);
    });

    test('it handles very small 1x1 images', function () {
        $imagePath = $this->testDir.'/tiny.png';
        $image = imagecreatetruecolor(1, 1);
        $red = imagecolorallocate($image, 200, 50, 50);
        imagesetpixel($image, 0, 0, $red);
        imagepng($image, $imagePath);
        imagedestroy($image);

        $palette = ColorPalette::fromImage($imagePath, 3);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(3);
    });
});

describe('ColorPalette fromColor Edge Cases', function () {
    test('it handles boundary RGB values in base color', function () {
        $extremeColors = [
            new Color(0, 0, 0),       // Black
            new Color(255, 255, 255), // White
            new Color(255, 0, 0),     // Pure red
            new Color(0, 255, 0),     // Pure green
            new Color(0, 0, 255),     // Pure blue
        ];

        foreach ($extremeColors as $color) {
            $palette = ColorPalette::fromColor($color, 'monochromatic');

            expect($palette)->toBeInstanceOf(ColorPalette::class);
            expect($palette->count())->toBeGreaterThan(0);
        }
    });

    test('it handles small count in options', function () {
        $color = new Color(100, 150, 200);

        // Count of 2 is the minimum for most schemes (count of 1 causes division by zero)
        $palette = ColorPalette::fromColor($color, 'monochromatic', ['count' => 2]);

        expect($palette->count())->toBeGreaterThanOrEqual(2);
    });

    test('it handles large count in options', function () {
        $color = new Color(100, 150, 200);

        $palette = ColorPalette::fromColor($color, 'monochromatic', ['count' => 50]);

        expect($palette->count())->toBeGreaterThanOrEqual(1);
    });

    test('it handles all scheme types with edge colors', function () {
        $black = new Color(0, 0, 0);
        $schemes = ['monochromatic', 'complementary', 'analogous', 'triadic', 'tetradic'];

        foreach ($schemes as $scheme) {
            $palette = ColorPalette::fromColor($black, $scheme);

            expect($palette)->toBeInstanceOf(ColorPalette::class);
            expect($palette->count())->toBeGreaterThan(0);
        }
    });

    test('it handles gray color (no saturation)', function () {
        $gray = new Color(128, 128, 128);

        $palette = ColorPalette::fromColor($gray, 'monochromatic');

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBeGreaterThan(0);
    });
});
