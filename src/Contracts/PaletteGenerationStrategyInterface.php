<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Contracts;

use Farzai\ColorPalette\ColorPalette;

/**
 * Strategy interface for palette generation algorithms
 *
 * This interface defines the contract for different palette generation strategies,
 * following the Strategy design pattern. Each strategy implements a specific
 * algorithm for generating color palettes from a base color.
 *
 * Benefits:
 * - Open/Closed Principle: New strategies can be added without modifying existing code
 * - Single Responsibility: Each strategy class handles one generation algorithm
 * - Testability: Strategies can be tested independently
 * - Flexibility: Strategies can be swapped at runtime
 */
interface PaletteGenerationStrategyInterface
{
    /**
     * Generate a color palette using the strategy's algorithm
     *
     * @param  ColorInterface  $baseColor  The base color to generate palette from
     * @param  array<string, mixed>  $options  Optional configuration for the strategy
     * @return ColorPalette The generated color palette
     */
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette;
}
