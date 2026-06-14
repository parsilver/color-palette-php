<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorPaletteInterface;
use Farzai\ColorPalette\Contracts\ThemeGeneratorInterface;
use Farzai\ColorPalette\Contracts\ThemeInterface;
use InvalidArgumentException;

class ThemeGenerator implements ThemeGeneratorInterface
{
    /**
     * Generate a theme from a color palette.
     *
     * If the palette is already keyed by the five theme roles (e.g. the output of
     * WebsiteThemeStrategy), it is lifted directly. Otherwise the five roles are
     * derived from the palette: the dominant colors become primary/secondary/accent
     * and neutral light tones are used for background/surface. The result always
     * defines all five roles, so every ThemeInterface getter resolves.
     *
     * @throws InvalidArgumentException If the palette is empty
     */
    public function generate(ColorPaletteInterface $palette): ThemeInterface
    {
        $colors = $palette->getColors();

        // Already a role-keyed palette -> lift straight into a validated Theme.
        $hasAllRoles = true;
        foreach (Theme::ROLES as $role) {
            if (! isset($colors[$role])) {
                $hasAllRoles = false;
                break;
            }
        }

        if ($hasAllRoles) {
            return Theme::fromPalette($palette);
        }

        // Derive the five roles from an arbitrary palette. Pairing is positional
        // (array_values), never key-based, so string- or gap-keyed palettes cannot
        // corrupt the result.
        $values = array_values($colors);

        if ($values === []) {
            throw new InvalidArgumentException('Cannot generate a theme from an empty palette');
        }

        $primary = $values[0];
        $secondary = $values[1] ?? $primary;
        $accent = $values[2] ?? $secondary;

        return Theme::fromRoles(
            $primary,
            $secondary,
            $accent,
            Color::fromHsl(0, 0, 98),  // near-white background
            Color::fromHsl(0, 0, 100), // white surface
        );
    }
}
