<?php

declare(strict_types=1);

use Farzai\ColorPalette\Exceptions\ImageException;
use Farzai\ColorPalette\Exceptions\ImageLoadException;
use Farzai\ColorPalette\Exceptions\InvalidImageException;

describe('ImageException', function () {
    it('can be instantiated', function () {
        $exception = new ImageException('Test message');

        expect($exception)->toBeInstanceOf(ImageException::class)
            ->and($exception)->toBeInstanceOf(\Exception::class);
    });

    it('can be thrown and caught', function () {
        try {
            throw new ImageException('Image processing failed');
        } catch (ImageException $e) {
            expect($e->getMessage())->toBe('Image processing failed');
        }
    });

    it('can store error message', function () {
        $message = 'Failed to process image data';
        $exception = new ImageException($message);

        expect($exception->getMessage())->toBe($message);
    });

    it('can store error code', function () {
        $exception = new ImageException('Error message', 500);

        expect($exception->getCode())->toBe(500);
    });

    it('can chain previous exceptions', function () {
        $previous = new \RuntimeException('Original error');
        $exception = new ImageException('Wrapped error', 0, $previous);

        expect($exception->getPrevious())->toBe($previous);
    });

    it('can be caught as base Exception', function () {
        try {
            throw new ImageException('Test error');
        } catch (\Exception $e) {
            expect($e)->toBeInstanceOf(ImageException::class);
        }
    });
});

describe('ImageLoadException', function () {
    it('can be instantiated', function () {
        $exception = new ImageLoadException('Load failed');

        expect($exception)->toBeInstanceOf(ImageLoadException::class)
            ->and($exception)->toBeInstanceOf(\Exception::class);
    });

    it('can be thrown and caught', function () {
        try {
            throw new ImageLoadException('Failed to load image file');
        } catch (ImageLoadException $e) {
            expect($e->getMessage())->toBe('Failed to load image file');
        }
    });

    it('can store detailed error message', function () {
        $message = 'Image file not found: /path/to/image.jpg';
        $exception = new ImageLoadException($message);

        expect($exception->getMessage())->toBe($message);
    });

    it('can store error code', function () {
        $exception = new ImageLoadException('Load error', 404);

        expect($exception->getCode())->toBe(404);
    });

    it('can chain previous exceptions', function () {
        $previous = new \Exception('File system error');
        $exception = new ImageLoadException('Cannot load image', 0, $previous);

        expect($exception->getPrevious())->toBe($previous)
            ->and($exception->getPrevious()->getMessage())->toBe('File system error');
    });

    it('is independent from ImageException', function () {
        $loadException = new ImageLoadException('Load error');
        $imageException = new ImageException('Processing error');

        expect($loadException)->not->toBe($imageException);
        expect($loadException::class)->not->toBe($imageException::class);
    });

    it('can be caught as base Exception', function () {
        try {
            throw new ImageLoadException('Test load error');
        } catch (\Exception $e) {
            expect($e)->toBeInstanceOf(ImageLoadException::class);
        }
    });
});

describe('InvalidImageException', function () {
    it('can be instantiated', function () {
        $exception = new InvalidImageException('Invalid image');

        expect($exception)->toBeInstanceOf(InvalidImageException::class)
            ->and($exception)->toBeInstanceOf(\Exception::class);
    });

    it('can be thrown and caught', function () {
        try {
            throw new InvalidImageException('Invalid image format');
        } catch (InvalidImageException $e) {
            expect($e->getMessage())->toBe('Invalid image format');
        }
    });

    it('can store validation error messages', function () {
        $message = 'Image dimensions are invalid: 0x0';
        $exception = new InvalidImageException($message);

        expect($exception->getMessage())->toBe($message);
    });

    it('can store error code', function () {
        $exception = new InvalidImageException('Invalid format', 400);

        expect($exception->getCode())->toBe(400);
    });

    it('can chain previous exceptions', function () {
        $previous = new \InvalidArgumentException('Bad parameter');
        $exception = new InvalidImageException('Image validation failed', 0, $previous);

        expect($exception->getPrevious())->toBe($previous)
            ->and($exception->getPrevious()->getMessage())->toBe('Bad parameter');
    });

    it('is independent from other image exceptions', function () {
        $invalidException = new InvalidImageException('Invalid');
        $loadException = new ImageLoadException('Load error');
        $imageException = new ImageException('Processing error');

        expect($invalidException)->not->toBe($loadException);
        expect($invalidException)->not->toBe($imageException);
        expect($invalidException::class)->not->toBe($loadException::class);
        expect($invalidException::class)->not->toBe($imageException::class);
    });

    it('can be caught as base Exception', function () {
        try {
            throw new InvalidImageException('Test invalid error');
        } catch (\Exception $e) {
            expect($e)->toBeInstanceOf(InvalidImageException::class);
        }
    });
});

describe('Exception hierarchy and relationships', function () {
    it('all exceptions extend base Exception', function () {
        $imageException = new ImageException('Test');
        $loadException = new ImageLoadException('Test');
        $invalidException = new InvalidImageException('Test');

        expect($imageException)->toBeInstanceOf(\Exception::class);
        expect($loadException)->toBeInstanceOf(\Exception::class);
        expect($invalidException)->toBeInstanceOf(\Exception::class);
    });

    it('exceptions can be differentiated in catch blocks', function () {
        $caught = [];

        try {
            throw new InvalidImageException('Invalid');
        } catch (InvalidImageException $e) {
            $caught[] = 'InvalidImageException';
        } catch (ImageLoadException $e) {
            $caught[] = 'ImageLoadException';
        } catch (ImageException $e) {
            $caught[] = 'ImageException';
        }

        expect($caught)->toBe(['InvalidImageException']);
    });

    it('can use specific exception types for error handling', function () {
        // Simulate realistic error handling scenario
        $processImage = function ($path) {
            if (empty($path)) {
                throw new InvalidImageException('Path cannot be empty');
            }

            if (! file_exists($path)) {
                throw new ImageLoadException("File not found: {$path}");
            }

            return true;
        };

        // Test invalid path
        try {
            $processImage('');
            expect(false)->toBeTrue(); // Should not reach here
        } catch (InvalidImageException $e) {
            expect($e->getMessage())->toContain('cannot be empty');
        }

        // Test file not found
        try {
            $processImage('/nonexistent/path.jpg');
            expect(false)->toBeTrue(); // Should not reach here
        } catch (ImageLoadException $e) {
            expect($e->getMessage())->toContain('File not found');
        }
    });
});

describe('Edge cases and boundary conditions', function () {
    it('handles empty error messages', function () {
        $exception = new ImageException('');

        expect($exception->getMessage())->toBe('');
    });

    it('handles very long error messages', function () {
        $longMessage = str_repeat('Error details: ', 1000);
        $exception = new InvalidImageException($longMessage);

        expect($exception->getMessage())->toBe($longMessage);
    });

    it('handles special characters in error messages', function () {
        $message = "Error with special chars: @#$%^&*()[]{}|\\/<>?\"'";
        $exception = new ImageLoadException($message);

        expect($exception->getMessage())->toBe($message);
    });

    it('handles unicode characters in error messages', function () {
        $message = 'Error: Файл не найден 文件未找到 ファイルが見つかりません';
        $exception = new ImageException($message);

        expect($exception->getMessage())->toBe($message);
    });

    it('handles zero as error code', function () {
        $exception = new ImageException('Error', 0);

        expect($exception->getCode())->toBe(0);
    });

    it('handles negative error codes', function () {
        $exception = new ImageLoadException('Error', -1);

        expect($exception->getCode())->toBe(-1);
    });

    it('handles deeply nested exception chains', function () {
        $level1 = new \Exception('Level 1');
        $level2 = new \RuntimeException('Level 2', 0, $level1);
        $level3 = new ImageException('Level 3', 0, $level2);
        $level4 = new InvalidImageException('Level 4', 0, $level3);

        expect($level4->getPrevious())->toBe($level3);
        expect($level4->getPrevious()->getPrevious())->toBe($level2);
        expect($level4->getPrevious()->getPrevious()->getPrevious())->toBe($level1);
    });
});
