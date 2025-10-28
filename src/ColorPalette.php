<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use ArrayAccess;
use Countable;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;

/**
 * @implements ArrayAccess<string|int, ColorInterface>
 */
class ColorPalette implements ArrayAccess, ColorPaletteInterface, Countable
{
    private const COLOR_WHITE = [255, 255, 255];

    private const COLOR_BLACK = [0, 0, 0];

    /**
     * @var array<string|int, ColorInterface>
     */
    private array $colors;

    private static ?Color $whiteColor = null;

    private static ?Color $blackColor = null;

    /**
     * @param  array<string|int, ColorInterface>  $colors
     */
    public function __construct(array $colors)
    {
        $this->colors = $colors;
    }

    /**
     * Extract colors from an image file
     *
     * This is a convenience method that simplifies the most common use case.
     * For more control, use ImageFactory and ColorExtractorFactory directly.
     *
     * @param  string  $path  Path to the image file
     * @param  int  $count  Number of colors to extract (default: 5)
     * @param  string  $driver  Image processing driver: 'gd' or 'imagick' (default: 'gd')
     *
     * @throws \InvalidArgumentException If the file doesn't exist or driver is invalid
     *
     * @example
     * ```php
     * $palette = ColorPalette::fromImage('photo.jpg', 5);
     * $colors = $palette->toArray();
     * ```
     */
    public static function fromImage(string $path, int $count = 5, string $driver = 'gd'): self
    {
        $imageFactory = new ImageFactory;
        $image = $imageFactory->createFromPath($path, $driver);

        $extractorFactory = new ColorExtractorFactory;
        $extractor = $extractorFactory->make($driver);

        $palette = $extractor->extract($image, $count);
        assert($palette instanceof self);

        return $palette;
    }

    /**
     * Generate a color palette from a base color using a scheme
     *
     * @param  ColorInterface  $color  Base color to generate palette from
     * @param  string  $scheme  Scheme name: 'monochromatic', 'complementary', 'analogous', 'triadic', etc.
     * @param  array<string, mixed>  $options  Scheme-specific options (e.g., ['count' => 7])
     *
     * @example
     * ```php
     * $palette = ColorPalette::fromColor(
     *     Color::fromHex('#3498db'),
     *     'monochromatic',
     *     ['count' => 7]
     * );
     * ```
     */
    public static function fromColor(ColorInterface $color, string $scheme = 'monochromatic', array $options = []): self
    {
        $generator = new PaletteGenerator($color);

        $count = isset($options['count']) && is_int($options['count']) ? $options['count'] : 5;

        return match ($scheme) {
            'monochromatic' => $generator->monochromatic($count),
            'complementary' => $generator->complementary(),
            'analogous' => $generator->analogous(),
            'triadic' => $generator->triadic(),
            'tetradic' => $generator->tetradic(),
            'split-complementary', 'splitComplementary' => $generator->splitComplementary(),
            'shades' => $generator->shades($count),
            'tints' => $generator->tints($count),
            'pastel' => $generator->pastel(),
            'vibrant' => $generator->vibrant(),
            'website-theme', 'websiteTheme' => $generator->websiteTheme(),
            default => throw new \InvalidArgumentException("Unknown scheme: {$scheme}"),
        };
    }

    /**
     * Create a new ColorPaletteBuilder instance
     *
     *
     * @example
     * ```php
     * $palette = ColorPalette::builder()
     *     ->fromImage('photo.jpg')
     *     ->withCount(5)
     *     ->build();
     * ```
     */
    public static function builder(): ColorPaletteBuilder
    {
        return ColorPaletteBuilder::create();
    }

    /**
     * Get white color instance (singleton for performance)
     */
    private static function getWhiteColor(): Color
    {
        if (self::$whiteColor === null) {
            self::$whiteColor = new Color(...self::COLOR_WHITE);
        }

        return self::$whiteColor;
    }

    /**
     * Get black color instance (singleton for performance)
     */
    private static function getBlackColor(): Color
    {
        if (self::$blackColor === null) {
            self::$blackColor = new Color(...self::COLOR_BLACK);
        }

        return self::$blackColor;
    }

    /**
     * @return array<string|int, ColorInterface>
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * @return array<string|int, string>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->colors as $key => $color) {
            $result[$key] = $color->toHex();
        }

        return $result;
    }

    public function count(): int
    {
        return count($this->colors);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->colors[$offset]);
    }

    public function offsetGet(mixed $offset): ColorInterface
    {
        if (! $this->offsetExists($offset)) {
            throw new \OutOfBoundsException(
                sprintf('Color at offset "%s" does not exist in palette', $offset)
            );
        }

        return $this->colors[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        /** @phpstan-ignore-next-line */
        if (! ($value instanceof ColorInterface)) {
            throw new \InvalidArgumentException('Value must be an instance of ColorInterface');
        }

        if ($offset === null) {
            $this->colors[] = $value;
        } else {
            $this->colors[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->colors[$offset]);
    }

    public function getSuggestedTextColor(ColorInterface $backgroundColor): ColorInterface
    {
        $lightColor = self::getWhiteColor();
        $darkColor = self::getBlackColor();

        $lightContrast = $backgroundColor->getContrastRatio($lightColor);
        $darkContrast = $backgroundColor->getContrastRatio($darkColor);

        return $lightContrast > $darkContrast ? $lightColor : $darkColor;
    }

    /**
     * @return array<string, ColorInterface>
     */
    public function getSuggestedSurfaceColors(): array
    {
        if (empty($this->colors)) {
            return [];
        }

        $colors = array_values($this->colors);
        usort($colors, fn (ColorInterface $a, ColorInterface $b) => $b->getBrightness() <=> $a->getBrightness());

        return [
            'surface' => $colors[0],
            'background' => isset($colors[1]) ? $colors[1] : $colors[0],
            'accent' => $this->findAccentColor($colors),
            'surface_variant' => $this->createVariant($colors[0], $colors[0]->isLight() ? -10 : 10),
        ];
    }

    /**
     * Find a suitable accent color from the palette
     *
     * @param  array<ColorInterface>  $colors
     */
    private function findAccentColor(array $colors): ColorInterface
    {
        $whiteColor = self::getWhiteColor();
        $blackColor = self::getBlackColor();

        foreach ($colors as $color) {
            $lightContrast = $color->getContrastRatio($whiteColor);
            $darkContrast = $color->getContrastRatio($blackColor);

            if ($lightContrast >= 3.0 && $darkContrast >= 3.0) {
                return $color;
            }
        }

        $middleIndex = (int) floor(count($colors) / 2);

        return $colors[$middleIndex];
    }

    private function createVariant(ColorInterface $color, int $adjustment): ColorInterface
    {
        $rgb = $color->toRgb();
        $factor = 1 + ($adjustment / 100);

        return new Color(
            (int) min(255, max(0, round($rgb['r'] * $factor))),
            (int) min(255, max(0, round($rgb['g'] * $factor))),
            (int) min(255, max(0, round($rgb['b'] * $factor)))
        );
    }
}
