<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;

/**
 * Fluent builder for creating ColorPalette instances
 *
 * This class implements the Builder design pattern, providing a fluent interface
 * for constructing ColorPalette objects with various configurations.
 *
 * Benefits:
 * - More readable and expressive code
 * - Step-by-step construction of complex objects
 * - Separation of construction logic from representation
 * - Follows the Builder design pattern
 *
 * Example usage:
 * ```php
 * $palette = ColorPaletteBuilder::create()
 *     ->addColor(Color::fromHex('#FF0000'))
 *     ->addColor(Color::fromHex('#00FF00'))
 *     ->addColor(Color::fromHex('#0000FF'))
 *     ->build();
 *
 * $palette = ColorPaletteBuilder::create()
 *     ->fromImage('path/to/image.jpg')
 *     ->withCount(5)
 *     ->build();
 *
 * $palette = ColorPaletteBuilder::create()
 *     ->withBaseColor(Color::fromHex('#FF5733'))
 *     ->withScheme('monochromatic', ['count' => 7])
 *     ->build();
 * ```
 */
class ColorPaletteBuilder
{
    /**
     * @var array<int|string, ColorInterface>
     */
    private array $colors = [];

    private ?ColorInterface $baseColor = null;

    private ?PaletteGenerationStrategyInterface $strategy = null;

    /**
     * @var array<string, mixed>
     */
    private array $strategyOptions = [];

    private ?string $imagePath = null;

    private int $extractionCount = 5;

    /**
     * Create a new builder instance
     */
    public static function create(): self
    {
        return new self;
    }

    /**
     * Add a single color to the palette
     *
     * @param  ColorInterface  $color  The color to add
     * @param  int|string|null  $key  Optional key for the color
     * @return $this
     */
    public function addColor(ColorInterface $color, int|string|null $key = null): self
    {
        if ($key === null) {
            $this->colors[] = $color;
        } else {
            $this->colors[$key] = $color;
        }

        return $this;
    }

    /**
     * Add multiple colors to the palette
     *
     * @param  array<int|string, ColorInterface>  $colors  Array of colors to add
     * @return $this
     */
    public function addColors(array $colors): self
    {
        foreach ($colors as $key => $color) {
            $this->addColor($color, is_string($key) ? $key : null);
        }

        return $this;
    }

    /**
     * Set the base color for palette generation
     *
     * @param  ColorInterface  $color  The base color
     * @return $this
     */
    public function withBaseColor(ColorInterface $color): self
    {
        $this->baseColor = $color;

        return $this;
    }

    /**
     * Set the color scheme/strategy for generation
     *
     * @param  string|PaletteGenerationStrategyInterface  $scheme  The scheme name or strategy instance
     * @param  array<string, mixed>  $options  Optional configuration for the strategy
     * @return $this
     */
    public function withScheme(string|PaletteGenerationStrategyInterface $scheme, array $options = []): self
    {
        if ($scheme instanceof PaletteGenerationStrategyInterface) {
            $this->strategy = $scheme;
        } else {
            $this->strategy = $this->resolveStrategy($scheme);
        }

        $this->strategyOptions = $options;

        return $this;
    }

    /**
     * Generate palette from an image file
     *
     * @param  string  $path  Path to the image file
     * @return $this
     */
    public function fromImage(string $path): self
    {
        $this->imagePath = $path;

        return $this;
    }

    /**
     * Set the number of colors to extract from image
     *
     * @param  int  $count  Number of colors to extract
     * @return $this
     */
    public function withCount(int $count): self
    {
        $this->extractionCount = $count;

        return $this;
    }

    /**
     * Build and return the ColorPalette instance
     *
     * @return ColorPalette The constructed palette
     */
    public function build(): ColorPalette
    {
        // Priority 1: Use manually added colors if available
        if (! empty($this->colors)) {
            return new ColorPalette($this->colors);
        }

        // Priority 2: Extract colors from image if specified
        if ($this->imagePath !== null) {
            return $this->buildFromImage();
        }

        // Priority 3: Generate using strategy if specified
        if ($this->baseColor !== null && $this->strategy !== null) {
            return $this->buildFromStrategy();
        }

        // Default: Return empty palette
        return new ColorPalette([]);
    }

    /**
     * Build palette from image extraction
     */
    private function buildFromImage(): ColorPalette
    {
        if ($this->imagePath === null) {
            return new ColorPalette([]);
        }

        $loader = (new ImageLoaderFactory)->create();
        $image = $loader->load($this->imagePath);

        $extractorFactory = new ColorExtractorFactory;
        $extractor = $extractorFactory->make('gd');

        $palette = $extractor->extract($image, $this->extractionCount);

        // The extractor always returns ColorPalette in practice
        assert($palette instanceof ColorPalette);

        return $palette;
    }

    /**
     * Build palette using generation strategy
     */
    private function buildFromStrategy(): ColorPalette
    {
        if ($this->strategy === null || $this->baseColor === null) {
            return new ColorPalette([]);
        }

        return $this->strategy->generate($this->baseColor, $this->strategyOptions);
    }

    /**
     * Resolve strategy name to strategy instance
     */
    private function resolveStrategy(string $name): PaletteGenerationStrategyInterface
    {
        return match (strtolower($name)) {
            'monochromatic' => new Strategies\MonochromaticStrategy,
            'complementary' => new Strategies\ComplementaryStrategy,
            'analogous' => new Strategies\AnalogousStrategy,
            'triadic' => new Strategies\TriadicStrategy,
            'tetradic' => new Strategies\TetradicStrategy,
            'split-complementary', 'splitcomplementary' => new Strategies\SplitComplementaryStrategy,
            'shades' => new Strategies\ShadesStrategy,
            'tints' => new Strategies\TintsStrategy,
            'pastel' => new Strategies\PastelStrategy,
            'vibrant' => new Strategies\VibrantStrategy,
            'website-theme', 'websitetheme' => new Strategies\WebsiteThemeStrategy,
            default => throw new \InvalidArgumentException("Unknown color scheme: {$name}"),
        };
    }
}
