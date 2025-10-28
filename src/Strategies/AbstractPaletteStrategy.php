<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;
use InvalidArgumentException;

/**
 * Abstract base class for palette generation strategies
 *
 * Provides common functionality for all strategy implementations including
 * options parsing and validation.
 */
abstract class AbstractPaletteStrategy implements PaletteGenerationStrategyInterface
{
    /**
     * Minimum allowed count value
     */
    protected const MIN_COUNT = 1;

    /**
     * Maximum allowed count value to prevent memory issues
     */
    protected const MAX_COUNT = 50;

    /**
     * Extract and validate the count option from the options array
     *
     * @param  array<string, mixed>  $options  The options array
     * @param  int  $default  Default value if count is not provided
     * @return int The validated count value
     *
     * @throws InvalidArgumentException If count is invalid
     */
    protected function getCountOption(array $options, int $default = 5): int
    {
        if (! isset($options['count'])) {
            return $default;
        }

        if (! is_numeric($options['count'])) {
            throw new InvalidArgumentException('Count option must be numeric');
        }

        $count = (int) $options['count'];

        if ($count < self::MIN_COUNT || $count > self::MAX_COUNT) {
            throw new InvalidArgumentException(
                sprintf(
                    'Count must be between %d and %d, got %d',
                    self::MIN_COUNT,
                    self::MAX_COUNT,
                    $count
                )
            );
        }

        return $count;
    }

    /**
     * Validate that a count value is within acceptable bounds
     *
     * @param  int  $count  The count to validate
     * @param  int  $min  Minimum allowed value
     * @param  int  $max  Maximum allowed value
     *
     * @throws InvalidArgumentException If count is out of bounds
     */
    protected function validateCount(int $count, int $min = self::MIN_COUNT, int $max = self::MAX_COUNT): void
    {
        if ($count < $min || $count > $max) {
            throw new InvalidArgumentException(
                sprintf('Count must be between %d and %d, got %d', $min, $max, $count)
            );
        }
    }
}
