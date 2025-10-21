---
layout: default
title: Color Harmony
description: Create harmonious color schemes using complementary, analogous, and custom harmony rules
parent: Examples
grand_parent: Home
nav_order: 6
keywords: color harmony, complementary colors, analogous colors, color theory
---

# Color Harmony

## Traditional Color Harmonies

### Complementary Harmony

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Harmony;

$baseColor = Color::fromHex('#2196f3');

// Create complementary harmony
$harmony = Harmony::complementary($baseColor);
$colors = $harmony->getColors();

// Get variations
$variations = $harmony->withVariations([
    'light' => fn($color) => $color->lighten(20),
    'dark' => fn($color) => $color->darken(20)
]);
```

### Analogous Harmony

```php
// Create analogous harmony with custom angle
$harmony = Harmony::analogous($baseColor)
    ->withAngle(30)  // 30° between colors
    ->withCount(3);  // Generate 3 colors

// Create with custom balance
$harmony = Harmony::analogous($baseColor)
    ->withBalance([
        'primary' => 0.5,    // 50% weight for primary
        'secondary' => 0.3,  // 30% weight for secondary
        'tertiary' => 0.2    // 20% weight for tertiary
    ]);
```

## Advanced Harmonies

### Split-Complementary with Variations

```php
// Create split-complementary with tints and shades
$harmony = Harmony::splitComplementary($baseColor)
    ->withVariations([
        'base' => [
            'normal' => fn($c) => $c,
            'light' => fn($c) => $c->lighten(15),
            'dark' => fn($c) => $c->darken(15)
        ],
        'split1' => [
            'normal' => fn($c) => $c,
            'muted' => fn($c) => $c->desaturate(20)
        ],
        'split2' => [
            'normal' => fn($c) => $c,
            'muted' => fn($c) => $c->desaturate(20)
        ]
    ]);
```

### Compound Harmony

```php
// Create compound harmony (Double Split Complementary)
$harmony = Harmony::compound($baseColor)
    ->withAngles([30, 150, 180, 210])
    ->withBalance([
        'primary' => 0.4,
        'secondary' => [0.2, 0.2],
        'accent' => [0.1, 0.1]
    ]);
```

## Dynamic Harmonies

### Temperature-Based Harmony

```php
class TemperatureHarmony {
    public static function create(Color $base, string $temperature): Harmony {
        $harmony = new Harmony($base);
        
        switch ($temperature) {
            case 'warm':
                return $harmony->withTransforms([
                    fn($c) => $c->adjustHue(30)->adjustSaturation(10),
                    fn($c) => $c->adjustHue(60)->adjustSaturation(20)
                ]);
            case 'cool':
                return $harmony->withTransforms([
                    fn($c) => $c->adjustHue(-30)->adjustSaturation(10),
                    fn($c) => $c->adjustHue(-60)->adjustSaturation(20)
                ]);
        }
    }
}

$warmHarmony = TemperatureHarmony::create($baseColor, 'warm');
$coolHarmony = TemperatureHarmony::create($baseColor, 'cool');
```

### Mood-Based Harmony

```php
class MoodHarmony {
    public static function create(Color $base, string $mood): Harmony {
        $harmony = new Harmony($base);
        
        $transforms = [
            'energetic' => [
                fn($c) => $c->adjustSaturation(20)->adjustLightness(10),
                fn($c) => $c->adjustHue(30)->adjustSaturation(30)
            ],
            'calm' => [
                fn($c) => $c->adjustSaturation(-20)->adjustLightness(10),
                fn($c) => $c->adjustHue(-30)->adjustSaturation(-10)
            ],
            'professional' => [
                fn($c) => $c->adjustSaturation(-10),
                fn($c) => $c->adjustLightness(-20)
            ]
        ];
        
        return $harmony->withTransforms($transforms[$mood] ?? []);
    }
}

$energeticHarmony = MoodHarmony::create($baseColor, 'energetic');
$calmHarmony = MoodHarmony::create($baseColor, 'calm');
```

## Harmony Analysis

### Harmony Quality Assessment

```php
class HarmonyAnalyzer {
    public static function analyze(Harmony $harmony): array {
        $colors = $harmony->getColors();
        $scores = [];
        
        // Analyze contrast relationships
        $scores['contrast'] = self::analyzeContrast($colors);
        
        // Analyze color distribution
        $scores['distribution'] = self::analyzeDistribution($colors);
        
        // Analyze color temperature
        $scores['temperature'] = self::analyzeTemperature($colors);
        
        return $scores;
    }
    
    private static function analyzeContrast(array $colors): float {
        $totalContrast = 0;
        $pairs = 0;
        
        foreach ($colors as $i => $color1) {
            foreach (array_slice($colors, $i + 1) as $color2) {
                $totalContrast += $color1->getContrastRatio($color2);
                $pairs++;
            }
        }
        
        return $pairs > 0 ? $totalContrast / $pairs : 0;
    }
    
    private static function analyzeDistribution(array $colors): float {
        // Analyze hue distribution
        $hues = array_map(fn($c) => $c->getHue(), $colors);
        return self::calculateDistributionScore($hues, 360);
    }
    
    private static function analyzeTemperature(array $colors): string {
        $warmCount = 0;
        $coolCount = 0;
        
        foreach ($colors as $color) {
            $hue = $color->getHue();
            if ($hue < 180) $warmCount++;
            else $coolCount++;
        }
        
        return $warmCount > $coolCount ? 'warm' : 'cool';
    }
}
```

## Best Practices

1. **Harmony Selection**
   - Choose harmonies based on the intended emotional impact
   - Consider the context and purpose of the color scheme
   - Test harmonies across different backgrounds and contexts

2. **Balance and Proportion**
   ```php
   // Use the 60-30-10 rule
   $harmony = Harmony::custom($baseColor)
       ->withBalance([
           'primary' => 0.6,    // 60% dominant color
           'secondary' => 0.3,  // 30% secondary color
           'accent' => 0.1      // 10% accent color
       ]);
   ```

3. **Accessibility Considerations**
   ```php
   // Ensure sufficient contrast in harmonies
   $harmony = Harmony::complementary($baseColor)
       ->withConstraints([
           'minContrast' => 4.5,  // WCAG AA standard
           'maxColors' => 5
       ]);
   ```

4. **Performance and Caching**
   ```php
   // Cache complex harmonies
   $harmony = Cache::remember('brand-harmony', function() use ($baseColor) {
       return Harmony::compound($baseColor)
           ->withVariations([/* ... */])
           ->generate();
   });
   ``` 