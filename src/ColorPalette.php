<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use ArrayAccess;
use Countable;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;

class ColorPalette implements ArrayAccess, ColorPaletteInterface, Countable
{
    /**
     * @var array<string|int, ColorInterface>
     */
    private array $colors;

    /**
     * @param  array<string|int, ColorInterface>  $colors
     */
    public function __construct(array $colors)
    {
        $this->colors = $colors;
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

    public function offsetGet(mixed $offset): ?ColorInterface
    {
        return $this->colors[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
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
        $lightColor = new Color(255, 255, 255); // White
        $darkColor = new Color(0, 0, 0);        // Black

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
        foreach ($colors as $color) {
            $lightContrast = $color->getContrastRatio(new Color(255, 255, 255));
            $darkContrast = $color->getContrastRatio(new Color(0, 0, 0));

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
