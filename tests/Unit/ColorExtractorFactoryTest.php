<?php

use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\GdColorExtractor;
use Farzai\ColorPalette\ImagickColorExtractor;

test('it creates GD extractor when GD is available', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is not available.');
    }

    $factory = new ColorExtractorFactory;
    $extractor = $factory->make();

    expect($extractor)->toBeInstanceOf(GdColorExtractor::class);
});

test('it creates GD extractor with explicit driver name', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is not available.');
    }

    $factory = new ColorExtractorFactory;
    $extractor = $factory->make('gd');

    expect($extractor)->toBeInstanceOf(GdColorExtractor::class);
});

test('it creates Imagick extractor when Imagick is available', function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }

    $factory = new ColorExtractorFactory;
    $extractor = $factory->make('imagick');

    expect($extractor)->toBeInstanceOf(ImagickColorExtractor::class);
});

test('it throws exception for invalid driver', function () {
    $factory = new ColorExtractorFactory;

    expect(fn () => $factory->make('invalid'))
        ->toThrow(InvalidArgumentException::class, 'Unsupported driver: invalid');
});

test('it throws exception for unsupported drivers', function () {
    $factory = new ColorExtractorFactory;

    $unsupportedDrivers = ['webp', 'svg', 'unknown', 'null', ''];

    foreach ($unsupportedDrivers as $driver) {
        expect(fn () => $factory->make($driver))
            ->toThrow(InvalidArgumentException::class);
    }
});

describe('ColorExtractorFactory - Constructor', function () {
    test('it can be constructed without arguments', function () {
        $factory = new ColorExtractorFactory;

        expect($factory)->toBeInstanceOf(ColorExtractorFactory::class);
    });

    test('it can be constructed with custom logger', function () {
        $logger = new \Psr\Log\NullLogger;
        $factory = new ColorExtractorFactory($logger);

        expect($factory)->toBeInstanceOf(ColorExtractorFactory::class);
    });

    test('it can be constructed with custom extension checker', function () {
        $checker = new \Farzai\ColorPalette\Services\ExtensionChecker;
        $factory = new ColorExtractorFactory(null, $checker);

        expect($factory)->toBeInstanceOf(ColorExtractorFactory::class);
    });

    test('it can be constructed with both custom logger and extension checker', function () {
        $logger = new \Psr\Log\NullLogger;
        $checker = new \Farzai\ColorPalette\Services\ExtensionChecker;
        $factory = new ColorExtractorFactory($logger, $checker);

        expect($factory)->toBeInstanceOf(ColorExtractorFactory::class);
    });
});

describe('ColorExtractorFactory - Extension Checking', function () {
    test('it checks GD extension when creating GD extractor', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $factory = new ColorExtractorFactory;

        // Should not throw since GD is available
        expect(fn () => $factory->make('gd'))->not->toThrow(\RuntimeException::class);
    });

    test('it checks Imagick extension when creating Imagick extractor', function () {
        if (extension_loaded('imagick')) {
            $this->markTestSkipped('This test requires Imagick to not be loaded.');
        }

        $factory = new ColorExtractorFactory;

        // Should throw since Imagick is not available
        expect(fn () => $factory->make('imagick'))
            ->toThrow(\RuntimeException::class, 'Imagick extension is not available');
    });

    test('it throws exception when GD is requested but not available', function () {
        if (extension_loaded('gd')) {
            $this->markTestSkipped('This test requires GD to not be loaded.');
        }

        $factory = new ColorExtractorFactory;

        expect(fn () => $factory->make('gd'))
            ->toThrow(\RuntimeException::class, 'GD extension is not available');
    });
});

describe('ColorExtractorFactory - Logger Integration', function () {
    test('it creates extractor with logger when provided', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $logger = new \Psr\Log\NullLogger;
        $factory = new ColorExtractorFactory($logger);
        $extractor = $factory->make('gd');

        expect($extractor)->toBeInstanceOf(GdColorExtractor::class);
    });
});

describe('ColorExtractorFactory - Default Behavior', function () {
    test('it uses gd as default driver when no driver specified', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $factory = new ColorExtractorFactory;
        $extractor = $factory->make();

        expect($extractor)->toBeInstanceOf(GdColorExtractor::class);
    });
});

describe('ColorExtractorFactory - Static Factory Methods', function () {
    test('it creates GD extractor using static default method', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $extractor = ColorExtractorFactory::default();

        expect($extractor)->toBeInstanceOf(GdColorExtractor::class);
    });

    test('it creates GD extractor using static gd method', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $extractor = ColorExtractorFactory::gd();

        expect($extractor)->toBeInstanceOf(GdColorExtractor::class);
    });

    test('it creates Imagick extractor using static imagick method', function () {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not available.');
        }

        $extractor = ColorExtractorFactory::imagick();

        expect($extractor)->toBeInstanceOf(ImagickColorExtractor::class);
    });

    test('it throws exception when GD is not available with static gd method', function () {
        if (extension_loaded('gd')) {
            $this->markTestSkipped('This test requires GD to not be loaded.');
        }

        expect(fn () => ColorExtractorFactory::gd())
            ->toThrow(\RuntimeException::class, 'GD extension is not available');
    });

    test('it throws exception when Imagick is not available with static imagick method', function () {
        if (extension_loaded('imagick')) {
            $this->markTestSkipped('This test requires Imagick to not be loaded.');
        }

        expect(fn () => ColorExtractorFactory::imagick())
            ->toThrow(\RuntimeException::class, 'Imagick extension is not available');
    });

    test('static default method is equivalent to gd method', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available.');
        }

        $defaultExtractor = ColorExtractorFactory::default();
        $gdExtractor = ColorExtractorFactory::gd();

        expect($defaultExtractor)->toBeInstanceOf(get_class($gdExtractor));
    });
});
