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
