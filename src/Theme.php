<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;
use Farzai\ColorPalette\Contracts\ThemeInterface;
use InvalidArgumentException;

class Theme implements ThemeInterface
{
    /**
     * The semantic roles every theme must define.
     */
    public const ROLES = ['primary', 'secondary', 'accent', 'background', 'surface'];

    /**
     * @var array<string, ColorInterface>
     */
    private array $colors;

    /**
     * @param  array<string, ColorInterface>  $colors  Must define every role in self::ROLES
     *
     * @throws InvalidArgumentException If a required role is missing or is not a ColorInterface
     */
    public function __construct(array $colors)
    {
        foreach (self::ROLES as $role) {
            if (! (($colors[$role] ?? null) instanceof ColorInterface)) {
                throw new InvalidArgumentException("Theme is missing required '{$role}' color");
            }
        }

        $this->colors = $colors;
    }

    /**
     * Create a theme from a role => color map (must define all five roles).
     *
     * @param  array<string, ColorInterface>  $colors
     *
     * @throws InvalidArgumentException If a required role is missing
     */
    public static function fromColors(array $colors): self
    {
        return new self($colors);
    }

    /**
     * Create a theme from the five named roles.
     */
    public static function fromRoles(
        ColorInterface $primary,
        ColorInterface $secondary,
        ColorInterface $accent,
        ColorInterface $background,
        ColorInterface $surface,
    ): self {
        return new self([
            'primary' => $primary,
            'secondary' => $secondary,
            'accent' => $accent,
            'background' => $background,
            'surface' => $surface,
        ]);
    }

    /**
     * Lift a role-keyed palette (e.g. WebsiteThemeStrategy output) into a theme.
     *
     * @throws InvalidArgumentException If the palette does not define all five roles
     */
    public static function fromPalette(ColorPaletteInterface $palette): self
    {
        return new self($palette->getColors());
    }

    /**
     * Get a color by name.
     *
     * @throws InvalidArgumentException If the name is not present in the theme
     */
    public function getColor(string $name): ColorInterface
    {
        if (! $this->hasColor($name)) {
            throw new InvalidArgumentException("Color '{$name}' not found in theme");
        }

        return $this->colors[$name];
    }

    /**
     * Check if a color exists in the theme.
     */
    public function hasColor(string $name): bool
    {
        return isset($this->colors[$name]);
    }

    /**
     * Get all colors in the theme.
     *
     * @return array<string, ColorInterface>
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * Convert theme to a role => hex array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->colors as $name => $color) {
            $result[$name] = $color->toHex();
        }

        return $result;
    }

    public function getPrimaryColor(): ColorInterface
    {
        return $this->colors['primary'];
    }

    public function getSecondaryColor(): ColorInterface
    {
        return $this->colors['secondary'];
    }

    public function getAccentColor(): ColorInterface
    {
        return $this->colors['accent'];
    }

    public function getBackgroundColor(): ColorInterface
    {
        return $this->colors['background'];
    }

    public function getSurfaceColor(): ColorInterface
    {
        return $this->colors['surface'];
    }
}
