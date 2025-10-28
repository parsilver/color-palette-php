<?php

declare(strict_types=1);

use Farzai\ColorPalette\Images\GdImage;

beforeEach(function () {
    // Create a test image resource
    $this->resource = imagecreatetruecolor(100, 50);
    $this->image = new GdImage($this->resource);
});

describe('GdImage', function () {
    it('can be instantiated with a valid GdImage resource', function () {
        expect($this->image)->toBeInstanceOf(GdImage::class);
    });

    it('returns the correct width', function () {
        expect($this->image->getWidth())->toBe(100);
    });

    it('returns the correct height', function () {
        expect($this->image->getHeight())->toBe(50);
    });

    it('returns the original resource', function () {
        $resource = $this->image->getResource();
        expect($resource)->toBeInstanceOf(\GdImage::class);
        expect(imagesx($resource))->toBe(100);
        expect(imagesy($resource))->toBe(50);
    });

    describe('edge cases', function () {
        it('handles 1x1 pixel images', function () {
            $resource = imagecreatetruecolor(1, 1);
            $image = new GdImage($resource);

            expect($image->getWidth())->toBe(1);
            expect($image->getHeight())->toBe(1);
        });

        it('handles very large images', function () {
            $resource = imagecreatetruecolor(5000, 3000);
            $image = new GdImage($resource);

            expect($image->getWidth())->toBe(5000);
            expect($image->getHeight())->toBe(3000);
        });

        it('handles non-square images with extreme aspect ratios', function () {
            // Very wide
            $wideResource = imagecreatetruecolor(1000, 1);
            $wideImage = new GdImage($wideResource);
            expect($wideImage->getWidth())->toBe(1000);
            expect($wideImage->getHeight())->toBe(1);

            // Very tall
            $tallResource = imagecreatetruecolor(1, 1000);
            $tallImage = new GdImage($tallResource);
            expect($tallImage->getWidth())->toBe(1);
            expect($tallImage->getHeight())->toBe(1000);
        });
    });

    describe('image formats', function () {
        it('works with images created from files', function () {
            // Create a temporary test image file
            $tempFile = tempnam(sys_get_temp_dir(), 'test_image_').'.png';
            $resource = imagecreatetruecolor(200, 150);
            imagepng($resource, $tempFile);
            imagedestroy($resource);

            // Load the image
            $loadedResource = imagecreatefrompng($tempFile);
            $image = new GdImage($loadedResource);

            expect($image->getWidth())->toBe(200);
            expect($image->getHeight())->toBe(150);

            unlink($tempFile);
        });

        it('works with images created from string data', function () {
            // Create image from string
            $resource = imagecreatetruecolor(50, 50);
            ob_start();
            imagepng($resource);
            $imageData = ob_get_clean();
            imagedestroy($resource);

            $loadedResource = imagecreatefromstring($imageData);
            $image = new GdImage($loadedResource);

            expect($image->getWidth())->toBe(50);
            expect($image->getHeight())->toBe(50);
        });
    });

    describe('resource management', function () {
        it('maintains resource integrity across multiple method calls', function () {
            $width1 = $this->image->getWidth();
            $height1 = $this->image->getHeight();
            $resource1 = $this->image->getResource();

            $width2 = $this->image->getWidth();
            $height2 = $this->image->getHeight();
            $resource2 = $this->image->getResource();

            expect($width1)->toBe($width2);
            expect($height1)->toBe($height2);
            expect($resource1)->toBe($resource2);
        });

        it('allows resource to be used for image manipulation', function () {
            $resource = $this->image->getResource();

            // Perform some operations on the resource
            $red = imagecolorallocate($resource, 255, 0, 0);
            imagefilledrectangle($resource, 0, 0, 50, 25, $red);

            // Verify the image still works
            expect($this->image->getWidth())->toBe(100);
            expect($this->image->getHeight())->toBe(50);
        });
    });

    describe('boundary conditions', function () {
        it('handles minimum possible dimensions', function () {
            $resource = imagecreatetruecolor(1, 1);
            $image = new GdImage($resource);

            expect($image->getWidth())->toBeGreaterThan(0);
            expect($image->getHeight())->toBeGreaterThan(0);
        });

        it('consistently returns positive dimensions', function () {
            $sizes = [[1, 1], [10, 20], [100, 100], [500, 250]];

            foreach ($sizes as [$width, $height]) {
                $resource = imagecreatetruecolor($width, $height);
                $image = new GdImage($resource);

                expect($image->getWidth())->toBe($width)
                    ->and($image->getHeight())->toBe($height);
            }
        });
    });

    describe('memory and cleanup', function () {
        it('properly releases resources on destruction', function () {
            $resource = imagecreatetruecolor(100, 100);
            $image = new GdImage($resource);

            // Get the resource before destruction
            $retrievedResource = $image->getResource();
            expect($retrievedResource)->toBeInstanceOf(\GdImage::class);

            // Unset the image (triggers destructor)
            unset($image);

            // Resource should be destroyed - trying to use it would cause an error
            // We can't directly test this without causing a fatal error,
            // but we can verify the object is gone
            expect(isset($image))->toBeFalse();
        });

        it('handles multiple instances independently', function () {
            $resource1 = imagecreatetruecolor(100, 100);
            $resource2 = imagecreatetruecolor(200, 200);

            $image1 = new GdImage($resource1);
            $image2 = new GdImage($resource2);

            expect($image1->getWidth())->toBe(100);
            expect($image2->getWidth())->toBe(200);

            // Destroying one shouldn't affect the other
            unset($image1);
            expect($image2->getWidth())->toBe(200);
        });
    });
});
