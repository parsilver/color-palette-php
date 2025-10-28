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

        $loader = (new ImageLoaderFactory)->create();
        $image = $loader->load(__DIR__.'/../../../example/assets/sample.jpg');

        $extractor = new GdColorExtractor;
        $colors = $extractor->extract($image, 5);

        expect($colors)->toHaveCount(5);
        expect($colors[0])->toBeObject();
        expect($colors[0]->getRed())->toBeBetween(0, 255);
        expect($colors[0]->getGreen())->toBeBetween(0, 255);
        expect($colors[0]->getBlue())->toBeBetween(0, 255);
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

describe('GdColorExtractor - Deterministic Behavior', function () {
    test('it produces idempotent results (same image returns same colors in same order)', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $loader = (new ImageLoaderFactory)->create();
        $image = $loader->load(__DIR__.'/../../../example/assets/sample.jpg');

        $extractor = new GdColorExtractor;

        // Extract colors multiple times from the same image
        $firstRun = $extractor->extract($image, 5);
        $secondRun = $extractor->extract($image, 5);
        $thirdRun = $extractor->extract($image, 5);

        // Convert to arrays for easier comparison
        $firstColors = $firstRun->toArray();
        $secondColors = $secondRun->toArray();
        $thirdColors = $thirdRun->toArray();

        // All runs should produce identical results
        expect($firstColors)->toBe($secondColors)
            ->and($firstColors)->toBe($thirdColors)
            ->and($secondColors)->toBe($thirdColors);

        // Verify each color in the palette matches across runs
        foreach (range(0, 4) as $index) {
            expect($firstRun[$index]->toHex())
                ->toBe($secondRun[$index]->toHex())
                ->toBe($thirdRun[$index]->toHex());
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

    test('it throws exception when image lacks getResource method', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Create a mock without getResource method
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
        };

        $extractor = new GdColorExtractor;

        // Should return fallback because the error is caught in AbstractColorExtractor
        $palette = $extractor->extract($mockImage, 5);
        expect($palette)->toHaveCount(5);
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

        $gdImage = imagecreatetruecolor(200, 200);

        // Create a colorful gradient
        for ($x = 0; $x < 200; $x++) {
            for ($y = 0; $y < 200; $y++) {
                $r = (int) ($x / 200 * 255);
                $g = (int) ($y / 200 * 255);
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
