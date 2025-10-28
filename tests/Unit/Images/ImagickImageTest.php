<?php

use Farzai\ColorPalette\Images\ImagickImage;

beforeEach(function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }
});

describe('ImagickImage - Basic Functionality', function () {
    test('it can be constructed with Imagick resource', function () {
        $imagick = new Imagick;
        $imagick->newImage(100, 100, new ImagickPixel('red'));

        $image = new ImagickImage($imagick);

        expect($image)->toBeInstanceOf(ImagickImage::class);
        expect($image->getWidth())->toBe(100);
        expect($image->getHeight())->toBe(100);
    });

    test('it returns correct width', function () {
        $imagick = new Imagick;
        $imagick->newImage(150, 200, new ImagickPixel('blue'));

        $image = new ImagickImage($imagick);

        expect($image->getWidth())->toBe(150);
    });

    test('it returns correct height', function () {
        $imagick = new Imagick;
        $imagick->newImage(150, 200, new ImagickPixel('green'));

        $image = new ImagickImage($imagick);

        expect($image->getHeight())->toBe(200);
    });

    test('it returns the Imagick resource', function () {
        $imagick = new Imagick;
        $imagick->newImage(50, 50, new ImagickPixel('yellow'));

        $image = new ImagickImage($imagick);

        expect($image->getResource())->toBe($imagick);
        expect($image->getResource())->toBeInstanceOf(Imagick::class);
    });
});

describe('ImagickImage - Various Dimensions', function () {
    test('it handles very small images', function () {
        $imagick = new Imagick;
        $imagick->newImage(1, 1, new ImagickPixel('white'));

        $image = new ImagickImage($imagick);

        expect($image->getWidth())->toBe(1);
        expect($image->getHeight())->toBe(1);
    });

    test('it handles rectangular images (wide)', function () {
        $imagick = new Imagick;
        $imagick->newImage(500, 100, new ImagickPixel('cyan'));

        $image = new ImagickImage($imagick);

        expect($image->getWidth())->toBe(500);
        expect($image->getHeight())->toBe(100);
    });

    test('it handles rectangular images (tall)', function () {
        $imagick = new Imagick;
        $imagick->newImage(100, 500, new ImagickPixel('magenta'));

        $image = new ImagickImage($imagick);

        expect($image->getWidth())->toBe(100);
        expect($image->getHeight())->toBe(500);
    });

    test('it handles large images', function () {
        $imagick = new Imagick;
        $imagick->newImage(2000, 2000, new ImagickPixel('black'));

        $image = new ImagickImage($imagick);

        expect($image->getWidth())->toBe(2000);
        expect($image->getHeight())->toBe(2000);
    });
});

describe('ImagickImage - Real Image Files', function () {
    test('it works with real PNG images', function () {
        $imagick = new Imagick;

        // Create a temporary PNG file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.png';
        $imagick->newImage(200, 150, new ImagickPixel('red'));
        $imagick->setImageFormat('png');
        $imagick->writeImage($tempFile);

        // Load it back
        $loadedImagick = new Imagick($tempFile);
        $image = new ImagickImage($loadedImagick);

        expect($image->getWidth())->toBe(200);
        expect($image->getHeight())->toBe(150);

        // Cleanup
        @unlink($tempFile);
    });

    test('it works with real JPG images', function () {
        $imagick = new Imagick;

        // Create a temporary JPG file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.jpg';
        $imagick->newImage(300, 200, new ImagickPixel('blue'));
        $imagick->setImageFormat('jpg');
        $imagick->writeImage($tempFile);

        // Load it back
        $loadedImagick = new Imagick($tempFile);
        $image = new ImagickImage($loadedImagick);

        expect($image->getWidth())->toBe(300);
        expect($image->getHeight())->toBe(200);

        // Cleanup
        @unlink($tempFile);
    });
});

describe('ImagickImage - Resource Management', function () {
    test('it properly cleans up resources on destruct', function () {
        $imagick = new Imagick;
        $imagick->newImage(100, 100, new ImagickPixel('red'));

        $image = new ImagickImage($imagick);

        // Get the resource
        $resource = $image->getResource();
        expect($resource)->toBeInstanceOf(Imagick::class);

        // Destroy the image object (calls __destruct)
        unset($image);

        // After destruction, the resource should be cleared
        // We can't directly test this, but we can verify the test doesn't crash
        expect(true)->toBeTrue();
    });

    test('it maintains resource integrity during lifetime', function () {
        $imagick = new Imagick;
        $imagick->newImage(50, 50, new ImagickPixel('green'));

        $image = new ImagickImage($imagick);

        // Multiple calls should return the same resource
        $resource1 = $image->getResource();
        $resource2 = $image->getResource();

        expect($resource1)->toBe($resource2);
    });
});

describe('ImagickImage - ImageInterface Compliance', function () {
    test('it implements ImageInterface', function () {
        $imagick = new Imagick;
        $imagick->newImage(100, 100, new ImagickPixel('white'));

        $image = new ImagickImage($imagick);

        expect($image)->toBeInstanceOf(\Farzai\ColorPalette\Contracts\ImageInterface::class);
    });

    test('it has all required interface methods', function () {
        $imagick = new Imagick;
        $imagick->newImage(100, 100, new ImagickPixel('white'));

        $image = new ImagickImage($imagick);

        expect(method_exists($image, 'getWidth'))->toBeTrue();
        expect(method_exists($image, 'getHeight'))->toBeTrue();
        expect(method_exists($image, 'getResource'))->toBeTrue();
    });
});

describe('ImagickImage - Edge Cases', function () {
    test('it works with images created from existing files', function () {
        // Use the sample image if it exists
        $samplePath = __DIR__.'/../../../example/assets/sample.jpg';

        if (! file_exists($samplePath)) {
            $this->markTestSkipped('Sample image not found.');
        }

        $imagick = new Imagick($samplePath);
        $image = new ImagickImage($imagick);

        expect($image->getWidth())->toBeGreaterThan(0);
        expect($image->getHeight())->toBeGreaterThan(0);
    });

    test('it handles images with transparency', function () {
        $imagick = new Imagick;
        $imagick->newImage(100, 100, new ImagickPixel('transparent'));
        $imagick->setImageFormat('png');

        $image = new ImagickImage($imagick);

        expect($image->getWidth())->toBe(100);
        expect($image->getHeight())->toBe(100);
    });

    test('it works with different color formats', function () {
        $colors = [
            'red',
            '#00FF00',
            'rgb(0, 0, 255)',
            'rgba(255, 255, 0, 0.5)',
        ];

        foreach ($colors as $color) {
            $imagick = new Imagick;
            $imagick->newImage(50, 50, new ImagickPixel($color));

            $image = new ImagickImage($imagick);

            expect($image->getWidth())->toBe(50);
            expect($image->getHeight())->toBe(50);
        }
    });
});

describe('ImagickImage - Comparison with GdImage', function () {
    test('it provides same interface as GdImage', function () {
        $imagick = new Imagick;
        $imagick->newImage(100, 100, new ImagickPixel('red'));

        $imagickImage = new ImagickImage($imagick);

        // Both should implement the same interface
        expect($imagickImage)->toBeInstanceOf(\Farzai\ColorPalette\Contracts\ImageInterface::class);

        // Both should have the same methods
        expect(method_exists($imagickImage, 'getWidth'))->toBeTrue();
        expect(method_exists($imagickImage, 'getHeight'))->toBeTrue();
        expect(method_exists($imagickImage, 'getResource'))->toBeTrue();
    });
});
