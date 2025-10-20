<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\Images\GdImage;
use Farzai\ColorPalette\Images\ImagickImage;

beforeEach(function () {
    // Create a simple test image
    $this->testImagePath = __DIR__.'/../test-image.png';

    if (! file_exists($this->testImagePath)) {
        if (extension_loaded('gd')) {
            $image = imagecreatetruecolor(10, 10);
            $red = imagecolorallocate($image, 255, 0, 0);
            imagefilledrectangle($image, 0, 0, 10, 10, $red);
            imagepng($image, $this->testImagePath);
            imagedestroy($image);
        }
    }
});

afterEach(function () {
    // Cleanup test image after tests
    if (isset($this->testImagePath) && file_exists($this->testImagePath)) {
        @unlink($this->testImagePath);
    }
});

describe('ImageFactory GD Driver', function () {
    test('it creates GD image from path with gd driver', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $image = ImageFactory::createFromPath($this->testImagePath, 'gd');

        expect($image)->toBeInstanceOf(GdImage::class);
    });

    test('it creates GD image by default when no driver specified', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $image = ImageFactory::createFromPath($this->testImagePath);

        expect($image)->toBeInstanceOf(GdImage::class);
    });

    test('it throws exception when GD extension is not available', function () {
        if (extension_loaded('gd')) {
            $this->markTestSkipped('This test requires GD to not be loaded.');
        }

        expect(fn () => ImageFactory::createFromPath($this->testImagePath, 'gd'))
            ->toThrow(RuntimeException::class, 'GD extension is not available');
    });

    test('it throws exception for invalid image file with gd driver', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $invalidPath = __DIR__.'/invalid-image.txt';
        file_put_contents($invalidPath, 'not an image');

        try {
            expect(fn () => ImageFactory::createFromPath($invalidPath, 'gd'))
                ->toThrow(InvalidArgumentException::class);
        } finally {
            @unlink($invalidPath);
        }
    });

    test('it throws exception for non-existent file', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        expect(fn () => ImageFactory::createFromPath('/non/existent/path.png', 'gd'))
            ->toThrow(InvalidArgumentException::class, 'Image file not found');
    });
});

describe('ImageFactory Imagick Driver', function () {
    test('it creates Imagick image from path with imagick driver', function () {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not available.');
        }

        $image = ImageFactory::createFromPath($this->testImagePath, 'imagick');

        expect($image)->toBeInstanceOf(ImagickImage::class);
    });

    test('it throws exception when Imagick extension is not available', function () {
        if (extension_loaded('imagick')) {
            $this->markTestSkipped('This test requires Imagick to not be loaded.');
        }

        expect(fn () => ImageFactory::createFromPath($this->testImagePath, 'imagick'))
            ->toThrow(RuntimeException::class, 'Imagick extension is not available');
    });

    test('it throws exception for invalid image file with imagick driver', function () {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not available.');
        }

        $invalidPath = __DIR__.'/invalid-image.txt';
        file_put_contents($invalidPath, 'not an image');

        try {
            expect(fn () => ImageFactory::createFromPath($invalidPath, 'imagick'))
                ->toThrow(InvalidArgumentException::class);
        } finally {
            @unlink($invalidPath);
        }
    });
});

describe('ImageFactory Driver Selection', function () {
    test('it throws exception for unsupported driver', function () {
        expect(fn () => ImageFactory::createFromPath($this->testImagePath, 'invalid'))
            ->toThrow(InvalidArgumentException::class, 'Unsupported driver: invalid');
    });

    test('it throws exception for unknown driver', function () {
        expect(fn () => ImageFactory::createFromPath($this->testImagePath, 'webp'))
            ->toThrow(InvalidArgumentException::class, 'Unsupported driver');
    });
});

describe('ImageFactory Edge Cases', function () {
    test('it handles various image formats with gd driver', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Test with different extensions
        $formats = [
            'png' => ['png', 'imagecreatetruecolor', 'imagepng'],
            'jpg' => ['jpg', 'imagecreatetruecolor', 'imagejpeg'],
        ];

        foreach ($formats as $ext => $info) {
            $testPath = __DIR__."/../test-image.{$ext}";

            if (function_exists($info[1]) && function_exists($info[2])) {
                $img = call_user_func($info[1], 10, 10);
                call_user_func($info[2], $img, $testPath);
                imagedestroy($img);

                $image = ImageFactory::createFromPath($testPath, 'gd');
                expect($image)->toBeInstanceOf(GdImage::class);

                @unlink($testPath);
            }
        }
    });
});
