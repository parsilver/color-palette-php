<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Constants\ColorSchemeConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Website theme color palette generation strategy
 *
 * Generates a complete website theme palette with semantic color names.
 * Includes primary, secondary, accent, background, and surface colors.
 */
class WebsiteThemeStrategy extends AbstractPaletteStrategy
{
    /**
     * Hue shift for secondary color (degrees from primary color)
     */
    private const SECONDARY_HUE_SHIFT = 30;

    /**
     * Saturation reduction for secondary color (percentage points)
     */
    private const SECONDARY_SATURATION_REDUCTION = 20;

    /**
     * Saturation boost for accent color (percentage points)
     */
    private const ACCENT_SATURATION_BOOST = 20;

    /**
     * Lightness for background color (nearly white)
     */
    private const BACKGROUND_LIGHTNESS = 98;

    /**
     * Lightness for surface color (pure white)
     */
    private const SURFACE_LIGHTNESS = 100;

    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        return new ColorPalette([
            'primary' => $baseColor,
            'secondary' => $baseColor
                ->rotate(self::SECONDARY_HUE_SHIFT)
                ->desaturate(self::SECONDARY_SATURATION_REDUCTION / 100),
            'accent' => $baseColor
                ->rotate(ColorSchemeConstants::COMPLEMENTARY_ANGLE)
                ->saturate(self::ACCENT_SATURATION_BOOST / 100),
            'background' => Color::fromHsl(0, 0, self::BACKGROUND_LIGHTNESS),
            'surface' => Color::fromHsl(0, 0, self::SURFACE_LIGHTNESS),
        ]);
    }
}
