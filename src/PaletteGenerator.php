<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;
use Farzai\ColorPalette\Strategies\AnalogousStrategy;
use Farzai\ColorPalette\Strategies\ComplementaryStrategy;
use Farzai\ColorPalette\Strategies\MonochromaticStrategy;
use Farzai\ColorPalette\Strategies\PastelStrategy;
use Farzai\ColorPalette\Strategies\ShadesStrategy;
use Farzai\ColorPalette\Strategies\SplitComplementaryStrategy;
use Farzai\ColorPalette\Strategies\TetradicStrategy;
use Farzai\ColorPalette\Strategies\TintsStrategy;
use Farzai\ColorPalette\Strategies\TriadicStrategy;
use Farzai\ColorPalette\Strategies\VibrantStrategy;
use Farzai\ColorPalette\Strategies\WebsiteThemeStrategy;

/**
 * Palette Generator using Strategy Pattern
 *
 * This class uses the Strategy design pattern to generate color palettes.
 * Each palette generation algorithm is encapsulated in its own strategy class,
 * making the code more maintainable, testable, and extensible.
 *
 * Benefits:
 * - Easy to add new palette generation algorithms without modifying this class
 * - Each strategy can be tested independently
 * - Strategies can be reused in different contexts
 * - Follows Open/Closed Principle and Single Responsibility Principle
 */
class PaletteGenerator
{
    private ColorInterface $baseColor;

    public function __construct(ColorInterface $baseColor)
    {
        $this->baseColor = $baseColor;
    }

    /**
     * Generate a palette using a custom strategy
     *
     * @param  PaletteGenerationStrategyInterface  $strategy  The strategy to use
     * @param  array<string, mixed>  $options  Optional configuration for the strategy
     * @return ColorPalette The generated palette
     */
    public function generate(PaletteGenerationStrategyInterface $strategy, array $options = []): ColorPalette
    {
        return $strategy->generate($this->baseColor, $options);
    }

    public function monochromatic(int $count = 5): ColorPalette
    {
        return $this->generate(new MonochromaticStrategy, ['count' => $count]);
    }

    public function complementary(): ColorPalette
    {
        return $this->generate(new ComplementaryStrategy);
    }

    public function analogous(): ColorPalette
    {
        return $this->generate(new AnalogousStrategy);
    }

    public function triadic(): ColorPalette
    {
        return $this->generate(new TriadicStrategy);
    }

    public function tetradic(): ColorPalette
    {
        return $this->generate(new TetradicStrategy);
    }

    public function splitComplementary(): ColorPalette
    {
        return $this->generate(new SplitComplementaryStrategy);
    }

    public function shades(int $count = 5): ColorPalette
    {
        return $this->generate(new ShadesStrategy, ['count' => $count]);
    }

    public function tints(int $count = 5): ColorPalette
    {
        return $this->generate(new TintsStrategy, ['count' => $count]);
    }

    public function pastel(): ColorPalette
    {
        return $this->generate(new PastelStrategy);
    }

    public function vibrant(): ColorPalette
    {
        return $this->generate(new VibrantStrategy);
    }

    public function websiteTheme(): ColorPalette
    {
        return $this->generate(new WebsiteThemeStrategy);
    }
}
