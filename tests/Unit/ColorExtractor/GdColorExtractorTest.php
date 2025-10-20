<?php

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\GdColorExtractor;
use Farzai\ColorPalette\ImageLoaderFactory;

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

    $image = new \Farzai\ColorPalette\Images\GdImage($gdImage);

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

        $image = new \Farzai\ColorPalette\Images\GdImage($gdImage);

        $extractor = new GdColorExtractor;
        $palette = $extractor->extract($image, 3);

        expect($palette)->toHaveCount(3);
    }
});
