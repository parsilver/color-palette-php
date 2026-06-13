<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;
use InvalidArgumentException;

/**
 * Central registry mapping scheme names to palette-generation strategies
 *
 * Previously the name -> strategy mapping was duplicated across
 * ColorPalette::fromColor() and ColorPaletteBuilder::resolveStrategy(), with
 * divergent alias spellings (e.g. "splitComplementary" vs "splitcomplementary").
 * Both now resolve through this single registry, so adding or renaming a scheme
 * is a one-line change and the accepted aliases are consistent everywhere.
 */
class StrategyRegistry
{
    /**
     * Canonical (normalized) scheme name => strategy class.
     *
     * @var array<string, class-string<PaletteGenerationStrategyInterface>>
     */
    private const STRATEGIES = [
        'monochromatic' => Strategies\MonochromaticStrategy::class,
        'complementary' => Strategies\ComplementaryStrategy::class,
        'analogous' => Strategies\AnalogousStrategy::class,
        'triadic' => Strategies\TriadicStrategy::class,
        'tetradic' => Strategies\TetradicStrategy::class,
        'splitcomplementary' => Strategies\SplitComplementaryStrategy::class,
        'shades' => Strategies\ShadesStrategy::class,
        'tints' => Strategies\TintsStrategy::class,
        'pastel' => Strategies\PastelStrategy::class,
        'vibrant' => Strategies\VibrantStrategy::class,
        'websitetheme' => Strategies\WebsiteThemeStrategy::class,
    ];

    /**
     * Resolve a scheme name (any supported spelling) to a strategy instance
     *
     * @throws InvalidArgumentException If the scheme name is not registered
     */
    public static function resolve(string $name): PaletteGenerationStrategyInterface
    {
        $key = self::normalize($name);

        if (! isset(self::STRATEGIES[$key])) {
            throw new InvalidArgumentException("Unknown color scheme: {$name}");
        }

        $class = self::STRATEGIES[$key];

        return new $class;
    }

    /**
     * Whether a scheme name (any supported spelling) is registered
     */
    public static function has(string $name): bool
    {
        return isset(self::STRATEGIES[self::normalize($name)]);
    }

    /**
     * List the canonical scheme names
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_keys(self::STRATEGIES);
    }

    /**
     * Normalize a scheme name so spelling variants map to the same key
     * (case-insensitive; '-', '_' and spaces are ignored).
     */
    private static function normalize(string $name): string
    {
        return str_replace(['-', '_', ' '], '', strtolower($name));
    }
}
