<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Services;

use RuntimeException;

/**
 * Service for checking PHP image processing extension availability
 *
 * This service centralizes all extension checking logic, making it easy to:
 * - Mock for testing
 * - Extend for new extensions
 * - Modify error messages centrally
 * - Track dependencies explicitly
 */
class ExtensionChecker
{
    /**
     * Check if the GD extension is loaded
     *
     * @throws RuntimeException If GD extension is not available
     */
    public function ensureGdLoaded(): void
    {
        if (! $this->isGdAvailable()) {
            throw new RuntimeException(
                'GD extension is not available. Please install or enable the GD extension. '.
                'For installation instructions, visit: https://www.php.net/manual/en/book.image.php'
            );
        }
    }

    /**
     * Check if the Imagick extension is loaded
     *
     * @throws RuntimeException If Imagick extension is not available
     */
    public function ensureImagickLoaded(): void
    {
        if (! $this->isImagickAvailable()) {
            throw new RuntimeException(
                'Imagick extension is not available. Please install or enable the Imagick extension. '.
                'For installation instructions, visit: https://www.php.net/manual/en/book.imagick.php'
            );
        }
    }

    /**
     * Detect the preferred image driver based on available extensions
     *
     * Priority: Imagick > GD
     * Imagick is preferred as it generally provides better quality and more features
     *
     * @return string The preferred driver ('imagick' or 'gd')
     *
     * @throws RuntimeException If no image processing extension is available
     */
    public function detectPreferredDriver(): string
    {
        if ($this->isImagickAvailable()) {
            return 'imagick';
        }

        if ($this->isGdAvailable()) {
            return 'gd';
        }

        throw new RuntimeException(
            'No supported image processing extension found. '.
            'Please install either GD (recommended) or Imagick extension. '.
            'For installation instructions, visit: https://www.php.net/manual/en/book.image.php'
        );
    }

    /**
     * Check if GD extension is available
     *
     * @return bool True if GD is loaded, false otherwise
     */
    public function isGdAvailable(): bool
    {
        return extension_loaded('gd');
    }

    /**
     * Check if Imagick extension is available
     *
     * @return bool True if Imagick is loaded, false otherwise
     */
    public function isImagickAvailable(): bool
    {
        return extension_loaded('imagick');
    }

    /**
     * Get all available image processing extensions
     *
     * @return array<string> List of available driver names
     */
    public function getAvailableDrivers(): array
    {
        $drivers = [];

        if ($this->isGdAvailable()) {
            $drivers[] = 'gd';
        }

        if ($this->isImagickAvailable()) {
            $drivers[] = 'imagick';
        }

        return $drivers;
    }

    /**
     * Check if a specific driver is available
     *
     * @param  string  $driver  The driver name ('gd' or 'imagick')
     * @return bool True if the driver is available, false otherwise
     */
    public function isDriverAvailable(string $driver): bool
    {
        return match ($driver) {
            'gd' => $this->isGdAvailable(),
            'imagick' => $this->isImagickAvailable(),
            default => false,
        };
    }
}
