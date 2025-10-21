---
layout: default
title: Custom Color Schemes
description: Build advanced custom color schemes with angle-based generation and golden ratio algorithms
parent: Examples
grand_parent: Home
nav_order: 7
keywords: custom color schemes, golden ratio, color algorithms, advanced palettes
---

# Advanced Color Schemes

## Custom Color Schemes

### Creating Custom Angle-Based Schemes

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Scheme;

$baseColor = Color::fromHex('#2196f3');

// Create a custom scheme with specific angles
$scheme = Scheme::custom($baseColor)
    ->withAngles([30, 60, 120, 180])
    ->generate();

// Create a split-complementary variant
$scheme = Scheme::custom($baseColor)
    ->withAngles([150, 210]) // Split around 180°
    ->generate();

// Create a custom tetrad
$scheme = Scheme::custom($baseColor)
    ->withAngles([90, 180, 270])
    ->withCount(4)
    ->generate();
```

### Dynamic Angle Generation

```php
// Generate evenly spaced angles
$angles = range(0, 360, 45); // Every 45 degrees

$scheme = Scheme::custom($baseColor)
    ->withAngles($angles)
    ->generate();

// Generate golden ratio angles
function goldenRatioAngles($count) {
    $goldenRatio = 137.5077663; // Golden angle
    $angles = [];
    for ($i = 1; $i < $count; $i++) {
        $angles[] = fmod($i * $goldenRatio, 360);
    }
    return $angles;
}

$scheme = Scheme::custom($baseColor)
    ->withAngles(goldenRatioAngles(5))
    ->generate();
```

## Advanced Monochromatic Schemes

### Custom Lightness Steps

```php
// Create a monochromatic scheme with custom steps
$scheme = Scheme::monochromatic($baseColor)
    ->withLightnessSteps([10, 30, 50, 70, 90])
    ->generate();

// Create a weighted monochromatic scheme
$scheme = Scheme::monochromatic($baseColor)
    ->withLightnessSteps([
        ['value' => 20, 'weight' => 2],
        ['value' => 40, 'weight' => 1],
        ['value' => 60, 'weight' => 1],
        ['value' => 80, 'weight' => 2]
    ])
    ->generate();
```

### Saturation Variations

```php
// Create a scheme with varying saturation
$scheme = Scheme::custom($baseColor)
    ->withTransforms([
        fn($color) => $color->withSaturation(25),
        fn($color) => $color->withSaturation(50),
        fn($color) => $color->withSaturation(75),
        fn($color) => $color->withSaturation(100)
    ])
    ->generate();
```

## Complex Color Relationships

### Double-Split Complementary

```php
// Create a double-split complementary scheme
$scheme = Scheme::custom($baseColor)
    ->withAngles([30, 150, 210, 330])
    ->withCount(5) // Including base color
    ->generate();
```

### Multi-Tonal Schemes

```php
// Create a multi-tonal scheme
$scheme = Scheme::custom($baseColor)
    ->withTransforms([
        // Warm variations
        fn($color) => $color->adjustHue(15)->adjustSaturation(10),
        fn($color) => $color->adjustHue(30)->adjustSaturation(20),
        // Cool variations
        fn($color) => $color->adjustHue(-15)->adjustSaturation(10),
        fn($color) => $color->adjustHue(-30)->adjustSaturation(20)
    ])
    ->generate();
```

## Advanced Color Harmonies

### Custom Color Harmony

```php
class CustomHarmony {
    public static function generate(Color $base, int $count = 5): array {
        $colors = [$base];
        $hsl = $base->toHsl();
        
        // Generate colors using golden ratio
        $goldenRatio = 0.618033988749895;
        
        for ($i = 1; $i < $count; $i++) {
            $hue = fmod($hsl[0] + ($goldenRatio * 360 * $i), 360);
            $colors[] = Color::fromHsl($hue, $hsl[1], $hsl[2]);
        }
        
        return $colors;
    }
}

// Use custom harmony
$colors = CustomHarmony::generate($baseColor);
$scheme = new Scheme($colors);
```

### Seasonal Color Schemes

```php
class SeasonalSchemes {
    public static function spring(Color $base): Scheme {
        return Scheme::custom($base)
            ->withTransforms([
                fn($color) => $color->adjustSaturation(10)->adjustLightness(10),
                fn($color) => $color->adjustHue(30)->adjustSaturation(20),
                fn($color) => $color->adjustHue(60)->adjustLightness(20),
                fn($color) => $color->adjustHue(90)->adjustSaturation(10)
            ])
            ->generate();
    }
    
    public static function autumn(Color $base): Scheme {
        return Scheme::custom($base)
            ->withTransforms([
                fn($color) => $color->adjustSaturation(-10)->adjustLightness(-10),
                fn($color) => $color->adjustHue(-30)->adjustSaturation(-20),
                fn($color) => $color->adjustHue(-60)->adjustLightness(-20),
                fn($color) => $color->adjustHue(-90)->adjustSaturation(-10)
            ])
            ->generate();
    }
}

$springScheme = SeasonalSchemes::spring($baseColor);
$autumnScheme = SeasonalSchemes::autumn($baseColor);
```

## Best Practices

1. **Color Harmony Guidelines**
   - Keep the number of colors manageable (typically 3-7)
   - Maintain consistent saturation and brightness relationships
   - Consider color psychology and cultural implications

2. **Performance Optimization**
   ```php
   // Cache complex schemes
   $scheme = Cache::remember('custom-scheme', function() use ($baseColor) {
       return Scheme::custom($baseColor)
           ->withAngles(goldenRatioAngles(5))
           ->generate();
   });
   ```

3. **Error Handling**
   ```php
   try {
       $scheme = Scheme::custom($baseColor)
           ->withAngles([400, 500]) // Invalid angles
           ->generate();
   } catch (\InvalidArgumentException $e) {
       // Handle invalid angle values
   }
   ```

4. **Scheme Validation**
   ```php
   class SchemeValidator {
       public static function validate(Scheme $scheme): bool {
           $colors = $scheme->getColors();
           
           // Check minimum contrast
           $minContrast = 4.5; // WCAG AA standard
           foreach ($colors as $color1) {
               foreach ($colors as $color2) {
                   if ($color1 === $color2) continue;
                   if ($color1->getContrastRatio($color2) < $minContrast) {
                       return false;
                   }
               }
           }
           
           return true;
       }
   }
   ``` 