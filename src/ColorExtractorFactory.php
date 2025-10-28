<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Services\ExtensionChecker;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ColorExtractorFactory
{
    private LoggerInterface $logger;

    private ExtensionChecker $extensionChecker;

    public function __construct(
        ?LoggerInterface $logger = null,
        ?ExtensionChecker $extensionChecker = null
    ) {
        $this->logger = $logger ?? new NullLogger;
        $this->extensionChecker = $extensionChecker ?? new ExtensionChecker;
    }

    /**
     * Create the default color extractor (GD)
     *
     * This is a convenience method equivalent to: (new ColorExtractorFactory())->make('gd')
     *
     *
     * @example
     * ```php
     * $extractor = ColorExtractorFactory::default();
     * $palette = $extractor->extract($image, 5);
     * ```
     */
    public static function default(): AbstractColorExtractor
    {
        return (new self)->make('gd');
    }

    /**
     * Create a GD color extractor
     *
     * This is a convenience method equivalent to: (new ColorExtractorFactory())->make('gd')
     *
     *
     * @example
     * ```php
     * $extractor = ColorExtractorFactory::gd();
     * $palette = $extractor->extract($image, 5);
     * ```
     */
    public static function gd(): AbstractColorExtractor
    {
        return (new self)->make('gd');
    }

    /**
     * Create an Imagick color extractor
     *
     * This is a convenience method equivalent to: (new ColorExtractorFactory())->make('imagick')
     *
     *
     * @example
     * ```php
     * $extractor = ColorExtractorFactory::imagick();
     * $palette = $extractor->extract($image, 5);
     * ```
     */
    public static function imagick(): AbstractColorExtractor
    {
        return (new self)->make('imagick');
    }

    /**
     * Create a new color extractor instance
     *
     * @throws InvalidArgumentException If an unsupported driver is specified
     */
    public function make(string $driver = 'gd'): AbstractColorExtractor
    {
        return match ($driver) {
            'gd' => $this->createGdExtractor(),
            'imagick' => $this->createImagickExtractor(),
            default => throw new InvalidArgumentException("Unsupported driver: {$driver}"),
        };
    }

    /**
     * Create GD color extractor
     */
    private function createGdExtractor(): GdColorExtractor
    {
        $this->extensionChecker->ensureGdLoaded();

        return new GdColorExtractor($this->logger);
    }

    /**
     * Create Imagick color extractor
     */
    private function createImagickExtractor(): ImagickColorExtractor
    {
        $this->extensionChecker->ensureImagickLoaded();

        return new ImagickColorExtractor($this->logger);
    }
}
