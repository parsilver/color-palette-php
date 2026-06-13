<?php

declare(strict_types=1);

use Farzai\ColorPalette\Constants\ImageConstants;
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\Images\GdImage;

/**
 * Build a minimal, byte-tiny PNG whose IHDR *declares* the given dimensions.
 * getimagesize() reads width/height straight from IHDR without decoding any
 * pixels, so this is exactly the shape of a decompression bomb: a few bytes
 * on disk that would allocate billions of pixels if a decoder ran.
 */
function fakePngWithDimensions(int $width, int $height): string
{
    $signature = "\x89PNG\r\n\x1a\n";

    $ihdrData = pack('N', $width).pack('N', $height)."\x08\x02\x00\x00\x00"; // 8-bit, truecolor
    $ihdr = pack('N', strlen($ihdrData)).'IHDR'.$ihdrData.pack('N', crc32('IHDR'.$ihdrData));

    $iend = pack('N', 0).'IEND'.pack('N', crc32('IEND'));

    return $signature.$ihdr.$iend;
}

function writeTempImage(string $bytes, string $suffix = '.png'): string
{
    $path = tempnam(sys_get_temp_dir(), 'imgtest_').$suffix;
    file_put_contents($path, $bytes);

    return $path;
}

describe('ImageFactory decompression-bomb / pixel-flood guard', function () {
    test('it rejects an image whose declared dimensions exceed the pixel cap', function () {
        // ~6.4 gigapixels declared in a handful of bytes on disk.
        $path = writeTempImage(fakePngWithDimensions(80000, 80000));

        try {
            expect(fn () => ImageFactory::fromPath($path, 'gd'))
                ->toThrow(InvalidArgumentException::class, 'dimensions too large');
        } finally {
            @unlink($path);
        }
    });

    test('it rejects an image whose width exceeds the per-dimension cap', function () {
        $path = writeTempImage(fakePngWithDimensions(ImageConstants::MAX_IMAGE_WIDTH + 1, 10));

        try {
            expect(fn () => ImageFactory::fromPath($path, 'gd'))
                ->toThrow(InvalidArgumentException::class, 'dimensions too large');
        } finally {
            @unlink($path);
        }
    });

    test('it still loads a normal, small image', function () {
        $gd = imagecreatetruecolor(4, 4);
        $path = tempnam(sys_get_temp_dir(), 'imgok_').'.png';
        imagepng($gd, $path);
        unset($gd); // GD images are auto-freed objects since PHP 8.0

        try {
            $image = ImageFactory::fromPath($path, 'gd');
            expect($image)->toBeInstanceOf(GdImage::class);
            expect($image->getWidth())->toBe(4);
        } finally {
            @unlink($path);
        }
    });
});

describe('ImageFactory Imagick path validation parity', function () {
    test('the Imagick path rejects oversized dimensions like the GD path', function () {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not available.');
        }

        $path = writeTempImage(fakePngWithDimensions(80000, 80000));

        try {
            expect(fn () => ImageFactory::fromPath($path, 'imagick'))
                ->toThrow(InvalidArgumentException::class, 'dimensions too large');
        } finally {
            @unlink($path);
        }
    });

    test('the Imagick path rejects a non-image file (size/MIME validation runs)', function () {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not available.');
        }

        $path = writeTempImage('this is definitely not an image', '.png');

        try {
            expect(fn () => ImageFactory::fromPath($path, 'imagick'))
                ->toThrow(InvalidArgumentException::class);
        } finally {
            @unlink($path);
        }
    });
});
