<?php

use Farzai\ColorPalette\Services\ExtensionChecker;

beforeEach(function () {
    $this->checker = new ExtensionChecker;
});

describe('ExtensionChecker - GD Extension', function () {
    test('it checks if GD extension is available', function () {
        $result = $this->checker->isGdAvailable();

        expect($result)->toBeBool();

        // If GD is loaded, it should return true
        if (extension_loaded('gd')) {
            expect($result)->toBeTrue();
        } else {
            expect($result)->toBeFalse();
        }
    });

    test('it ensures GD is loaded when available', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        // Should not throw exception when GD is available
        expect(fn () => $this->checker->ensureGdLoaded())->not->toThrow(RuntimeException::class);
    });

    test('it throws exception when GD is not loaded', function () {
        if (extension_loaded('gd')) {
            $this->markTestSkipped('This test requires GD to not be loaded.');
        }

        expect(fn () => $this->checker->ensureGdLoaded())
            ->toThrow(RuntimeException::class, 'GD extension is not available');
    });

    test('it provides installation instructions in GD exception message', function () {
        if (extension_loaded('gd')) {
            $this->markTestSkipped('This test requires GD to not be loaded.');
        }

        try {
            $this->checker->ensureGdLoaded();
            $this->fail('Expected RuntimeException to be thrown');
        } catch (RuntimeException $e) {
            expect($e->getMessage())->toContain('https://www.php.net/manual/en/book.image.php');
        }
    });
});

describe('ExtensionChecker - Imagick Extension', function () {
    test('it checks if Imagick extension is available', function () {
        $result = $this->checker->isImagickAvailable();

        expect($result)->toBeBool();

        // If Imagick is loaded, it should return true
        if (extension_loaded('imagick')) {
            expect($result)->toBeTrue();
        } else {
            expect($result)->toBeFalse();
        }
    });

    test('it ensures Imagick is loaded when available', function () {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not available.');
        }

        // Should not throw exception when Imagick is available
        expect(fn () => $this->checker->ensureImagickLoaded())->not->toThrow(RuntimeException::class);
    });

    test('it throws exception when Imagick is not loaded', function () {
        if (extension_loaded('imagick')) {
            $this->markTestSkipped('This test requires Imagick to not be loaded.');
        }

        expect(fn () => $this->checker->ensureImagickLoaded())
            ->toThrow(RuntimeException::class, 'Imagick extension is not available');
    });

    test('it provides installation instructions in Imagick exception message', function () {
        if (extension_loaded('imagick')) {
            $this->markTestSkipped('This test requires Imagick to not be loaded.');
        }

        try {
            $this->checker->ensureImagickLoaded();
            $this->fail('Expected RuntimeException to be thrown');
        } catch (RuntimeException $e) {
            expect($e->getMessage())->toContain('https://www.php.net/manual/en/book.imagick.php');
        }
    });
});

describe('ExtensionChecker - Driver Detection', function () {
    test('it detects preferred driver when extensions are available', function () {
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            $this->markTestSkipped('No image processing extension available.');
        }

        $driver = $this->checker->detectPreferredDriver();

        expect($driver)->toBeString();
        expect($driver)->toBeIn(['gd', 'imagick']);

        // Imagick is preferred over GD
        if (extension_loaded('imagick')) {
            expect($driver)->toBe('imagick');
        } elseif (extension_loaded('gd')) {
            expect($driver)->toBe('gd');
        }
    });

    test('it prefers imagick over gd when both are available', function () {
        if (! extension_loaded('imagick') || ! extension_loaded('gd')) {
            $this->markTestSkipped('Both GD and Imagick extensions must be available.');
        }

        $driver = $this->checker->detectPreferredDriver();

        expect($driver)->toBe('imagick');
    });

    test('it returns gd when only gd is available', function () {
        if (! extension_loaded('gd') || extension_loaded('imagick')) {
            $this->markTestSkipped('Only GD extension should be available for this test.');
        }

        $driver = $this->checker->detectPreferredDriver();

        expect($driver)->toBe('gd');
    });

    test('it throws exception when no extension is available', function () {
        if (extension_loaded('gd') || extension_loaded('imagick')) {
            $this->markTestSkipped('This test requires no image processing extensions.');
        }

        expect(fn () => $this->checker->detectPreferredDriver())
            ->toThrow(RuntimeException::class, 'No supported image processing extension found');
    });

    test('it provides installation instructions when no extension is available', function () {
        if (extension_loaded('gd') || extension_loaded('imagick')) {
            $this->markTestSkipped('This test requires no image processing extensions.');
        }

        try {
            $this->checker->detectPreferredDriver();
            $this->fail('Expected RuntimeException to be thrown');
        } catch (RuntimeException $e) {
            expect($e->getMessage())->toContain('Please install either GD');
            expect($e->getMessage())->toContain('https://www.php.net/manual/en/book.image.php');
        }
    });
});

describe('ExtensionChecker - Available Drivers', function () {
    test('it returns list of available drivers', function () {
        $drivers = $this->checker->getAvailableDrivers();

        expect($drivers)->toBeArray();

        // Check that returned drivers match actual loaded extensions
        if (extension_loaded('gd')) {
            expect($drivers)->toContain('gd');
        } else {
            expect($drivers)->not->toContain('gd');
        }

        if (extension_loaded('imagick')) {
            expect($drivers)->toContain('imagick');
        } else {
            expect($drivers)->not->toContain('imagick');
        }
    });

    test('it returns empty array when no extensions are available', function () {
        if (extension_loaded('gd') || extension_loaded('imagick')) {
            $this->markTestSkipped('This test requires no image processing extensions.');
        }

        $drivers = $this->checker->getAvailableDrivers();

        expect($drivers)->toBeArray();
        expect($drivers)->toBeEmpty();
    });

    test('it returns both drivers when both extensions are available', function () {
        if (! extension_loaded('gd') || ! extension_loaded('imagick')) {
            $this->markTestSkipped('Both GD and Imagick extensions must be available.');
        }

        $drivers = $this->checker->getAvailableDrivers();

        expect($drivers)->toBeArray();
        expect($drivers)->toHaveCount(2);
        expect($drivers)->toContain('gd');
        expect($drivers)->toContain('imagick');
    });

    test('it returns only gd when only gd is available', function () {
        if (! extension_loaded('gd') || extension_loaded('imagick')) {
            $this->markTestSkipped('Only GD extension should be available for this test.');
        }

        $drivers = $this->checker->getAvailableDrivers();

        expect($drivers)->toBeArray();
        expect($drivers)->toHaveCount(1);
        expect($drivers)->toContain('gd');
    });

    test('it returns only imagick when only imagick is available', function () {
        if (extension_loaded('gd') || ! extension_loaded('imagick')) {
            $this->markTestSkipped('Only Imagick extension should be available for this test.');
        }

        $drivers = $this->checker->getAvailableDrivers();

        expect($drivers)->toBeArray();
        expect($drivers)->toHaveCount(1);
        expect($drivers)->toContain('imagick');
    });
});

describe('ExtensionChecker - Specific Driver Availability', function () {
    test('it checks if gd driver is available', function () {
        $isAvailable = $this->checker->isDriverAvailable('gd');

        expect($isAvailable)->toBeBool();

        if (extension_loaded('gd')) {
            expect($isAvailable)->toBeTrue();
        } else {
            expect($isAvailable)->toBeFalse();
        }
    });

    test('it checks if imagick driver is available', function () {
        $isAvailable = $this->checker->isDriverAvailable('imagick');

        expect($isAvailable)->toBeBool();

        if (extension_loaded('imagick')) {
            expect($isAvailable)->toBeTrue();
        } else {
            expect($isAvailable)->toBeFalse();
        }
    });

    test('it returns false for unsupported driver names', function () {
        expect($this->checker->isDriverAvailable('invalid'))->toBeFalse();
        expect($this->checker->isDriverAvailable('webp'))->toBeFalse();
        expect($this->checker->isDriverAvailable('unknown'))->toBeFalse();
        expect($this->checker->isDriverAvailable(''))->toBeFalse();
    });

    test('it is case sensitive for driver names', function () {
        // These should return false as the match is case-sensitive
        expect($this->checker->isDriverAvailable('GD'))->toBeFalse();
        expect($this->checker->isDriverAvailable('Imagick'))->toBeFalse();
        expect($this->checker->isDriverAvailable('IMAGICK'))->toBeFalse();
    });
});

describe('ExtensionChecker - Integration Tests', function () {
    test('it provides consistent results across methods', function () {
        // isGdAvailable should match isDriverAvailable('gd')
        expect($this->checker->isGdAvailable())
            ->toBe($this->checker->isDriverAvailable('gd'));

        // isImagickAvailable should match isDriverAvailable('imagick')
        expect($this->checker->isImagickAvailable())
            ->toBe($this->checker->isDriverAvailable('imagick'));
    });

    test('it has consistent driver list and individual checks', function () {
        $availableDrivers = $this->checker->getAvailableDrivers();

        // Each driver in the list should be available when checked individually
        foreach ($availableDrivers as $driver) {
            expect($this->checker->isDriverAvailable($driver))->toBeTrue(
                "Driver '{$driver}' is in available list but isDriverAvailable() returns false"
            );
        }

        // Check that unavailable drivers are not in the list
        $allPossibleDrivers = ['gd', 'imagick'];
        foreach ($allPossibleDrivers as $driver) {
            if (! $this->checker->isDriverAvailable($driver)) {
                expect($availableDrivers)->not->toContain($driver,
                    "Driver '{$driver}' is not available but appears in available drivers list"
                );
            }
        }
    });

    test('it has consistent detection and availability checks', function () {
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            $this->markTestSkipped('At least one image processing extension must be available.');
        }

        $preferredDriver = $this->checker->detectPreferredDriver();

        // The preferred driver must be available
        expect($this->checker->isDriverAvailable($preferredDriver))->toBeTrue();

        // The preferred driver must be in the available drivers list
        expect($this->checker->getAvailableDrivers())->toContain($preferredDriver);
    });
});
