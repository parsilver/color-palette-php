<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Exceptions\ExtensionNotLoadedException;
use Farzai\ColorPalette\GdColorExtractor;
use Farzai\ColorPalette\Images\GdImage;
use Farzai\ColorPalette\Images\ImagickImage;
use Farzai\ColorPalette\ImagickColorExtractor;

test('can create color extractor factory', function () {
    $factory = new ColorExtractorFactory;
    expect($factory)->toBeInstanceOf(ColorExtractorFactory::class);
});

test('can create GD color extractor for GD image', function () {
    $gdImage = new GdImage(imagecreatetruecolor(100, 100));
    $extractor = ColorExtractorFactory::createForImage($gdImage);
    expect($extractor)->toBeInstanceOf(GdColorExtractor::class);
});

test('can create Imagick color extractor for Imagick image', function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension not available');

        return;
    }

    /** @var \Imagick $imagick */
    $imagick = new \Imagick;
    /** @var \ImagickPixel $pixel */
    $pixel = new \ImagickPixel('red');
    $imagick->newImage(100, 100, $pixel);
    $imagickImage = new ImagickImage($imagick);

    $extractor = ColorExtractorFactory::createForImage($imagickImage);
    expect($extractor)->toBeInstanceOf(ImagickColorExtractor::class);

    // Clean up
    $imagick->destroy();
    $pixel->destroy();
});

test('throws exception when creating ImagickColorExtractor without extension', function () {
    if (extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is available');

        return;
    }

    expect(fn () => new ImagickColorExtractor)
        ->toThrow(ExtensionNotLoadedException::class, 'The Imagick extension is required');
});

test('can extract colors from GD image', function () {
    // Create a test image with known colors
    $width = 100;
    $height = 100;
    $image = imagecreatetruecolor($width, $height);

    // Fill with red color
    $red = imagecolorallocate($image, 255, 0, 0);
    imagefill($image, 0, 0, $red);

    $gdImage = new GdImage($image);
    $extractor = ColorExtractorFactory::createForImage($gdImage);

    $colorPalette = $extractor->extract($gdImage);
    expect($colorPalette)->toBeInstanceOf(ColorPalette::class);

    $colors = $colorPalette->getColors();
    expect($colors)->toBeArray();
    expect($colors[0])->toBeInstanceOf(Color::class);

    $rgb = $colors[0]->toRgb();
    expect($rgb['r'])->toBe(255);
    expect($rgb['g'])->toBe(0);
    expect($rgb['b'])->toBe(0);

    // Clean up
    imagedestroy($image);
});

test('throws exception for unsupported image type', function () {
    $mockImage = new class implements ImageInterface
    {
        public function getResource(): mixed
        {
            return null;
        }

        public function destroy(): void {}

        public function getWidth(): int
        {
            return 100;
        }

        public function getHeight(): int
        {
            return 100;
        }
    };

    expect(fn () => ColorExtractorFactory::createForImage($mockImage))
        ->toThrow(\InvalidArgumentException::class, 'Unsupported image type');
});

test('can handle empty image', function () {
    $width = 1;
    $height = 1;
    $image = imagecreatetruecolor($width, $height);

    // Create transparent image
    imagealphablending($image, false);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);

    $gdImage = new GdImage($image);
    $extractor = ColorExtractorFactory::createForImage($gdImage);

    $colorPalette = $extractor->extract($gdImage);
    expect($colorPalette)->toBeInstanceOf(ColorPalette::class);
    expect($colorPalette->getColors())->toHaveCount(5); // Should return default palette

    imagedestroy($image);
});

test('can handle single color image', function () {
    $width = 100;
    $height = 100;
    $image = imagecreatetruecolor($width, $height);

    // Fill with pure blue
    $blue = imagecolorallocate($image, 0, 0, 255);
    imagefill($image, 0, 0, $blue);

    $gdImage = new GdImage($image);
    $extractor = ColorExtractorFactory::createForImage($gdImage);

    $colorPalette = $extractor->extract($gdImage, 1); // Request only 1 color
    expect($colorPalette)->toBeInstanceOf(ColorPalette::class);

    $colors = $colorPalette->getColors();
    expect($colors)->toHaveCount(1);

    $rgb = $colors[0]->toRgb();
    expect($rgb['r'])->toBe(0);
    expect($rgb['g'])->toBe(0);
    expect($rgb['b'])->toBe(255);

    imagedestroy($image);
});

test('respects minimum saturation threshold', function () {
    $width = 100;
    $height = 100;
    $image = imagecreatetruecolor($width, $height);

    // Fill with very desaturated color (almost gray)
    $desaturated = imagecolorallocate($image, 128, 130, 126);
    imagefill($image, 0, 0, $desaturated);

    $gdImage = new GdImage($image);
    $extractor = ColorExtractorFactory::createForImage($gdImage);

    $colorPalette = $extractor->extract($gdImage);
    expect($colorPalette)->toBeInstanceOf(ColorPalette::class);
    expect($colorPalette->getColors())->toHaveCount(5); // Should return default palette due to low saturation

    imagedestroy($image);
});

test('can extract multiple distinct colors', function () {
    $width = 100;
    $height = 100;
    $image = imagecreatetruecolor($width, $height);

    // Create a 4-quadrant image with different colors
    $red = imagecolorallocate($image, 255, 0, 0);
    $green = imagecolorallocate($image, 0, 255, 0);
    $blue = imagecolorallocate($image, 0, 0, 255);
    $yellow = imagecolorallocate($image, 255, 255, 0);

    imagefilledrectangle($image, 0, 0, $width / 2, $height / 2, $red);
    imagefilledrectangle($image, $width / 2, 0, $width, $height / 2, $green);
    imagefilledrectangle($image, 0, $height / 2, $width / 2, $height, $blue);
    imagefilledrectangle($image, $width / 2, $height / 2, $width, $height, $yellow);

    $gdImage = new GdImage($image);
    $extractor = ColorExtractorFactory::createForImage($gdImage);

    $colorPalette = $extractor->extract($gdImage, 4);
    expect($colorPalette)->toBeInstanceOf(ColorPalette::class);

    $colors = $colorPalette->getColors();
    expect($colors)->toHaveCount(4);

    // Verify that all expected colors are present (order may vary)
    $foundColors = array_map(fn ($color) => $color->toRgb(), $colors);
    $expectedColors = [
        ['r' => 255, 'g' => 0, 'b' => 0],    // Red
        ['r' => 0, 'g' => 255, 'b' => 0],    // Green
        ['r' => 0, 'g' => 0, 'b' => 255],    // Blue
        ['r' => 255, 'g' => 255, 'b' => 0],  // Yellow
    ];

    foreach ($expectedColors as $expected) {
        $found = false;
        foreach ($foundColors as $actual) {
            if (abs($actual['r'] - $expected['r']) <= 5 &&
                abs($actual['g'] - $expected['g']) <= 5 &&
                abs($actual['b'] - $expected['b']) <= 5) {
                $found = true;
                break;
            }
        }
        expect($found)->toBeTrue("Expected color RGB({$expected['r']},{$expected['g']},{$expected['b']}) not found");
    }

    imagedestroy($image);
});
