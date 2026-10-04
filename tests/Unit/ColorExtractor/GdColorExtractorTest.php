<?php

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\GdColorExtractor;
use Farzai\ColorPalette\ImageLoaderFactory;
use Farzai\ColorPalette\Images\GdImage;

describe('GdColorExtractor - Basic Extraction', function () {
    test('it can extract colors from image', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Pin the driver: on hosts with Imagick the loader would otherwise hand
        // GdColorExtractor an ImagickImage and the test would only see the
        // grayscale fallback palette.
        $loader = (new ImageLoaderFactory(preferredDriver: 'gd'))->create();
        $image = $loader->load(__DIR__.'/../../../example/assets/sample.jpg');

        $extractor = new GdColorExtractor;
        $colors = $extractor->extract($image, 5);

        expect($colors)->toHaveCount(5);
        expect($colors[0])->toBeObject();
        expect($colors[0]->getRed())->toBeBetween(0, 255);
        expect($colors[0]->getGreen())->toBeBetween(0, 255);
        expect($colors[0]->getBlue())->toBeBetween(0, 255);
        // sample.jpg is solid red; the grayscale fallback would mean extraction failed.
        expect($colors[0]->getRed())->toBeGreaterThan(200)
            ->and($colors[0]->getGreen())->toBeLessThan(50);
    });

    test('it can extract different numbers of colors', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($gdImage, 255, 0, 0);
        imagefilledrectangle($gdImage, 0, 0, 100, 100, $red);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;

        foreach ([1, 3, 5, 10] as $count) {
            $palette = $extractor->extract($image, $count);
            expect($palette)->toHaveCount($count);
        }
    });
});

describe('GdColorExtractor - Error Handling', function () {
    test('it returns fallback palette when given wrong image type', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Create a mock that doesn't implement GdImage
        $mockImage = new class implements ImageInterface
        {
            public function getWidth(): int
            {
                return 100;
            }

            public function getHeight(): int
            {
                return 100;
            }

            public function getResource(): mixed
            {
                return null;
            }
        };

        $extractor = new GdColorExtractor;

        // When given wrong image type, AbstractColorExtractor catches exception
        // and returns fallback grayscale palette
        $palette = $extractor->extract($mockImage, 5);

        expect($palette)->toHaveCount(5);
        // Should return grayscale fallback
        expect($palette[0]->toHex())->toBe('#ffffff');
    });
});

describe('GdColorExtractor - Color Filtering', function () {
    test('it skips pure black and white pixels during extraction', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Create an image with vibrant colors to ensure extraction works
        $gdImage = imagecreatetruecolor(100, 100);

        // Fill with red
        $red = imagecolorallocate($gdImage, 255, 0, 0);
        imagefilledrectangle($gdImage, 0, 0, 50, 50, $red);

        // Fill with green
        $green = imagecolorallocate($gdImage, 0, 255, 0);
        imagefilledrectangle($gdImage, 51, 0, 100, 50, $green);

        // Fill with blue
        $blue = imagecolorallocate($gdImage, 0, 0, 255);
        imagefilledrectangle($gdImage, 0, 51, 100, 100, $blue);

        $image = new GdImage($gdImage);

        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 3);

        // Should extract the vibrant colors
        expect($palette)->toHaveCount(3);

        // Verify we got actual colors (RGB values vary)
        foreach ($palette as $color) {
            expect($color->getRed())->toBeBetween(0, 255);
            expect($color->getGreen())->toBeBetween(0, 255);
            expect($color->getBlue())->toBeBetween(0, 255);
        }
    });

    test('it correctly handles images with black and white pixels', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(100, 100);

        // Add some black pixels
        $black = imagecolorallocate($gdImage, 0, 0, 0);
        imagefilledrectangle($gdImage, 0, 0, 30, 30, $black);

        // Add some white pixels
        $white = imagecolorallocate($gdImage, 255, 255, 255);
        imagefilledrectangle($gdImage, 31, 0, 60, 30, $white);

        // Add a colored region
        $red = imagecolorallocate($gdImage, 200, 50, 50);
        imagefilledrectangle($gdImage, 0, 31, 100, 100, $red);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 3);

        expect($palette)->toHaveCount(3);
    });
});

describe('GdColorExtractor - Image Size Handling', function () {
    test('it handles various image sizes', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $sizes = [
            [10, 10],    // Very small
            [100, 100],  // Small
            [500, 500],  // Medium
        ];

        foreach ($sizes as [$width, $height]) {
            $gdImage = imagecreatetruecolor($width, $height);
            $red = imagecolorallocate($gdImage, 255, 0, 0);
            imagefilledrectangle($gdImage, 0, 0, $width, $height, $red);

            $image = new GdImage($gdImage);

            $extractor = new GdColorExtractor;
            $palette = $extractor->extract($image, 3);

            expect($palette)->toHaveCount(3);
        }
    });

    test('it handles single pixel images', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(1, 1);
        $red = imagecolorallocate($gdImage, 200, 50, 100);
        imagesetpixel($gdImage, 0, 0, $red);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 3);

        expect($palette)->toHaveCount(3);
    });

    test('it adjusts sampling based on image size', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Test that larger images use appropriate sampling
        $gdImage = imagecreatetruecolor(1000, 1000);

        // Create a gradient
        for ($x = 0; $x < 1000; $x++) {
            for ($y = 0; $y < 1000; $y++) {
                $r = (int) ($x / 1000 * 255);
                $g = (int) ($y / 1000 * 255);
                $color = imagecolorallocate($gdImage, $r, $g, 100);
                imagesetpixel($gdImage, $x, $y, $color);
            }
        }

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 5);

        expect($palette)->toHaveCount(5);
        // Verify we got diverse colors
        $hexColors = $palette->toArray();
        expect(count(array_unique($hexColors)))->toBeGreaterThan(1);
    });
});

describe('GdColorExtractor - Complex Color Patterns', function () {
    test('it extracts colors from multi-colored images', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(200, 200);

        // Create a diverse color palette in the image
        $colors = [
            [255, 0, 0],    // Red
            [0, 255, 0],    // Green
            [0, 0, 255],    // Blue
            [255, 255, 0],  // Yellow
            [255, 0, 255],  // Magenta
            [0, 255, 255],  // Cyan
        ];

        $sectionWidth = 200 / count($colors);
        foreach ($colors as $index => $rgb) {
            $color = imagecolorallocate($gdImage, $rgb[0], $rgb[1], $rgb[2]);
            imagefilledrectangle(
                $gdImage,
                (int) ($index * $sectionWidth),
                0,
                (int) (($index + 1) * $sectionWidth),
                200,
                $color
            );
        }

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 6);

        expect($palette)->toHaveCount(6);

        // Verify we got actual colors (not all the same)
        $hexColors = $palette->toArray();
        // Should have at least 2 different colors
        expect(count(array_unique($hexColors)))->toBeGreaterThanOrEqual(2);
    });

    test('it handles images with similar colors', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Create image with very similar shades of red
        $gdImage = imagecreatetruecolor(100, 100);

        for ($i = 0; $i < 10; $i++) {
            $r = 200 + $i * 5;
            $color = imagecolorallocate($gdImage, $r, 50, 50);
            imagefilledrectangle($gdImage, $i * 10, 0, ($i + 1) * 10, 100, $color);
        }

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 5);

        expect($palette)->toHaveCount(5);
    });
});

describe('GdColorExtractor - Edge Cases', function () {
    test('it handles all-black images', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(100, 100);
        $black = imagecolorallocate($gdImage, 0, 0, 0);
        imagefilledrectangle($gdImage, 0, 0, 100, 100, $black);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 5);

        // Should return a fallback palette since all pixels are black
        expect($palette)->toHaveCount(5);

        // Verify it's a grayscale fallback palette
        foreach ($palette as $color) {
            expect($color->getRed())->toBeBetween(0, 255);
            expect($color->getGreen())->toBe($color->getRed());
            expect($color->getBlue())->toBe($color->getRed());
        }
    });

    test('it handles all-white images', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(100, 100);
        $white = imagecolorallocate($gdImage, 255, 255, 255);
        imagefilledrectangle($gdImage, 0, 0, 100, 100, $white);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 5);

        // Should return a fallback palette since all pixels are white
        expect($palette)->toHaveCount(5);

        // Verify it's a grayscale fallback palette
        foreach ($palette as $color) {
            expect($color->getRed())->toBeBetween(0, 255);
            expect($color->getGreen())->toBe($color->getRed());
            expect($color->getBlue())->toBe($color->getRed());
        }
    });

    test('it handles grayscale images', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(100, 100);

        // Create grayscale gradient
        for ($i = 0; $i < 10; $i++) {
            $gray = (int) ($i * 25);
            $color = imagecolorallocate($gdImage, $gray, $gray, $gray);
            imagefilledrectangle($gdImage, $i * 10, 0, ($i + 1) * 10, 100, $color);
        }

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 5);

        expect($palette)->toHaveCount(5);

        // Colors should be grayscale (R = G = B)
        foreach ($palette as $color) {
            expect($color->getRed())->toBe($color->getGreen());
            expect($color->getGreen())->toBe($color->getBlue());
        }
    });

    test('it handles images with very few distinct colors', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Create image with only 2 colors but request 5
        $gdImage = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($gdImage, 255, 0, 0);
        $blue = imagecolorallocate($gdImage, 0, 0, 255);

        imagefilledrectangle($gdImage, 0, 0, 50, 100, $red);
        imagefilledrectangle($gdImage, 51, 0, 100, 100, $blue);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 5);

        // Should still return 5 colors even if source has fewer distinct colors
        expect($palette)->toHaveCount(5);
    });

    test('it handles extremely small 2x2 images', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(2, 2);
        $red = imagecolorallocate($gdImage, 255, 0, 0);
        imagefilledrectangle($gdImage, 0, 0, 2, 2, $red);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 3);

        expect($palette)->toHaveCount(3);
    });

    test('it handles very tall images', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(10, 1000);
        $red = imagecolorallocate($gdImage, 200, 50, 50);
        imagefilledrectangle($gdImage, 0, 0, 10, 1000, $red);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 3);

        expect($palette)->toHaveCount(3);
    });

    test('it handles very wide images', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(1000, 10);
        $blue = imagecolorallocate($gdImage, 50, 50, 200);
        imagefilledrectangle($gdImage, 0, 0, 1000, 10, $blue);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 3);

        expect($palette)->toHaveCount(3);
    });
});

describe('GdColorExtractor - Boundary Count Values', function () {
    test('it handles count of 1', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($gdImage, 255, 0, 0);
        imagefilledrectangle($gdImage, 0, 0, 100, 100, $red);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 1);

        expect($palette)->toHaveCount(1);
        expect($palette[0])->toBeObject();
    });

    test('it handles maximum count of 50', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // 2,500 distinct colours are plenty for 50 clusters and keep the ten
        // k-means restarts of a 50-colour extraction cheap.
        $gdImage = imagecreatetruecolor(50, 50);

        // Create a colorful gradient
        for ($x = 0; $x < 50; $x++) {
            for ($y = 0; $y < 50; $y++) {
                $r = (int) ($x / 50 * 255);
                $g = (int) ($y / 50 * 255);
                $b = 128;
                $color = imagecolorallocate($gdImage, $r, $g, $b);
                imagesetpixel($gdImage, $x, $y, $color);
            }
        }

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 50);

        expect($palette)->toHaveCount(50);

        // All should be valid color objects
        foreach ($palette as $color) {
            expect($color)->toBeObject();
            expect($color->getRed())->toBeBetween(0, 255);
        }
    });
});

describe('GdColorExtractor - Transparency Handling', function () {
    test('it handles images with transparency', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(100, 100);
        imagealphablending($gdImage, false);
        imagesavealpha($gdImage, true);

        // Create transparent background
        $transparent = imagecolorallocatealpha($gdImage, 0, 0, 0, 127);
        imagefilledrectangle($gdImage, 0, 0, 100, 100, $transparent);

        // Add some opaque colors
        $red = imagecolorallocate($gdImage, 255, 0, 0);
        imagefilledrectangle($gdImage, 25, 25, 75, 75, $red);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 3);

        expect($palette)->toHaveCount(3);
    });

    test('it ignores the colour under fully transparent pixels of a truecolor PNG', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Five opaque stripes on a fully transparent background that hides a
        // saturated green. Only the stripes are visible, so the palette must be
        // the one of the stripes alone.
        $stripes = [[220, 40, 40], [40, 80, 200], [230, 200, 40], [150, 60, 170], [240, 140, 60]];
        $canvas = imagecreatetruecolor(100, 100);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefilledrectangle($canvas, 0, 0, 99, 99, imagecolorallocatealpha($canvas, 0, 200, 0, 127));
        $stripesOnly = imagecreatetruecolor(60, 60);
        foreach ($stripes as $i => [$r, $g, $b]) {
            imagefilledrectangle($canvas, 20 + $i * 12, 20, 31 + $i * 12, 79, imagecolorallocate($canvas, $r, $g, $b));
            imagefilledrectangle($stripesOnly, $i * 12, 0, $i * 12 + 11, 59, imagecolorallocate($stripesOnly, $r, $g, $b));
        }

        ob_start();
        imagepng($canvas);
        $png = imagecreatefromstring((string) ob_get_clean());

        // The PNG round trip must keep the green under the transparent pixels,
        // or there is nothing left for the extractor to ignore.
        expect(imageistruecolor($png))->toBeTrue()
            ->and(imagecolorsforindex($png, imagecolorat($png, 0, 0)))
            ->toBe(['red' => 0, 'green' => 200, 'blue' => 0, 'alpha' => 127]);

        $extractor = new GdColorExtractor;

        expect($extractor->extract(new GdImage($png), 5)->toArray())
            ->toBe($extractor->extract(new GdImage($stripesOnly), 5)->toArray());
    });

    test('it ignores the transparent palette index of a GIF', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // The same stripes as a GIF whose background is a transparent palette
        // index holding green.
        $stripes = [[220, 40, 40], [40, 80, 200], [230, 200, 40], [150, 60, 170], [240, 140, 60]];
        $canvas = imagecreate(100, 100);
        // The first colour allocated to a palette image fills its background.
        imagecolortransparent($canvas, imagecolorallocate($canvas, 0, 200, 0));
        $stripesOnly = imagecreatetruecolor(60, 60);
        foreach ($stripes as $i => [$r, $g, $b]) {
            imagefilledrectangle($canvas, 20 + $i * 12, 20, 31 + $i * 12, 79, imagecolorallocate($canvas, $r, $g, $b));
            imagefilledrectangle($stripesOnly, $i * 12, 0, $i * 12 + 11, 59, imagecolorallocate($stripesOnly, $r, $g, $b));
        }

        ob_start();
        imagegif($canvas);
        $gif = imagecreatefromstring((string) ob_get_clean());

        // The GIF round trip must keep the background as a transparent index
        // that still holds green.
        $transparent = imagecolortransparent($gif);
        expect(imageistruecolor($gif))->toBeFalse()
            ->and(imagecolorat($gif, 0, 0))->toBe($transparent)
            ->and(imagecolorsforindex($gif, $transparent))
            ->toBe(['red' => 0, 'green' => 200, 'blue' => 0, 'alpha' => 127]);

        $extractor = new GdColorExtractor;

        expect($extractor->extract(new GdImage($gif), 5)->toArray())
            ->toBe($extractor->extract(new GdImage($stripesOnly), 5)->toArray());
    });

    test('it falls back to the grayscale palette when every pixel is fully transparent', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $trueColor = imagecreatetruecolor(50, 50);
        imagealphablending($trueColor, false);
        imagefilledrectangle($trueColor, 0, 0, 49, 49, imagecolorallocatealpha($trueColor, 0, 200, 0, 127));

        $paletteImage = imagecreate(50, 50);
        imagecolortransparent($paletteImage, imagecolorallocate($paletteImage, 0, 200, 0));

        $extractor = new GdColorExtractor;
        $fallback = ['#ffffff', '#c7c7c7', '#8f8f8f', '#565656', '#1e1e1e'];

        expect($extractor->extract(new GdImage($trueColor), 5)->toArray())->toBe($fallback)
            ->and($extractor->extract(new GdImage($paletteImage), 5)->toArray())->toBe($fallback);
    });

    test('it weights partially transparent pixels by their opacity', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Left half opaque red, right half blue at alpha 85 of 127, so each
        // blue pixel is 42/127 opaque. A single swatch is then the opacity-
        // weighted mean, (220 * 127 + 40 * 42) / 169 = 175 red and
        // (40 * 127 + 220 * 42) / 169 = 85 blue, not the plain mean #822882.
        $trueColor = imagecreatetruecolor(100, 100);
        imagealphablending($trueColor, false);
        $paletteImage = imagecreate(100, 100);
        foreach ([$trueColor, $paletteImage] as $gdImage) {
            imagefilledrectangle($gdImage, 0, 0, 49, 99, imagecolorallocate($gdImage, 220, 40, 40));
            imagefilledrectangle($gdImage, 50, 0, 99, 99, imagecolorallocatealpha($gdImage, 40, 40, 220, 85));
        }

        $extractor = new GdColorExtractor;

        expect($extractor->extract(new GdImage($trueColor), 1)->toArray())->toBe(['#af2855'])
            ->and($extractor->extract(new GdImage($paletteImage), 1)->toArray())->toBe(['#af2855']);
    });
});

describe('GdColorExtractor - Consistency and Reproducibility', function () {
    test('it produces consistent results with same input parameters', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $gdImage = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($gdImage, 200, 50, 50);
        imagefilledrectangle($gdImage, 0, 0, 100, 100, $red);

        $image = new GdImage($gdImage);
        $extractor = new GdColorExtractor;

        // Extract multiple times with same parameters
        $results = [];
        for ($i = 0; $i < 5; $i++) {
            $results[] = $extractor->extract($image, 3);
        }

        // All results should have same count
        foreach ($results as $palette) {
            expect($palette)->toHaveCount(3);
        }

        // First color hex should be identical across all runs
        $firstHex = $results[0][0]->toHex();
        foreach ($results as $palette) {
            expect($palette[0]->toHex())->toBe($firstHex);
        }
    });
});

describe('GdColorExtractor - Palette Images', function () {
    test('it reads palette-based images (GIF, PNG-8) as colours, not palette indexes', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // imagecreate() gives the same palette (non-truecolor) image type that
        // imagecreatefromstring() returns for GIF and PNG-8 uploads.
        $stripes = [[220, 40, 40], [40, 160, 60], [40, 80, 200], [230, 200, 40], [150, 60, 170]];
        $trueColor = imagecreatetruecolor(60, 60);
        $paletteImage = imagecreate(60, 60);
        foreach ($stripes as $i => [$r, $g, $b]) {
            imagefilledrectangle($trueColor, $i * 12, 0, $i * 12 + 11, 59, imagecolorallocate($trueColor, $r, $g, $b));
            imagefilledrectangle($paletteImage, $i * 12, 0, $i * 12 + 11, 59, imagecolorallocate($paletteImage, $r, $g, $b));
        }
        expect(imageistruecolor($paletteImage))->toBeFalse();

        $extractor = new GdColorExtractor;
        $expected = $extractor->extract(new GdImage($trueColor), 5)->toArray();

        expect($extractor->extract(new GdImage($paletteImage), 5)->toArray())->toBe($expected);
    });
    test('it skips pixels whose palette index the palette does not define', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // A hand-written PNG-8 whose PLTE defines two colours while a fifth of
        // the pixels use index 3. libpng loads it; the undefined index must not
        // throw the whole image onto the grayscale fallback.
        $chunk = fn (string $type, string $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $raw = '';
        for ($y = 0; $y < 100; $y++) {
            $raw .= "\0";
            for ($x = 0; $x < 100; $x++) {
                $raw .= chr($x < 50 ? 0 : ($x < 80 ? 1 : 3));
            }
        }
        $png = "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', 100, 100, 8, 3, 0, 0, 0))
            .$chunk('PLTE', "\xC8\x3C\x32\x28\x78\xB4")
            .$chunk('IDAT', (string) gzcompress($raw))
            .$chunk('IEND', '');

        $gdImage = @imagecreatefromstring($png);
        expect($gdImage)->toBeInstanceOf(\GdImage::class);

        $hexes = array_map(
            fn ($color) => $color->toHex(),
            (new GdColorExtractor)->extract(new GdImage($gdImage), 2)->getColors()
        );

        expect($hexes)->toEqualCanonicalizing(['#c83c32', '#2878b4']);
    });
});
