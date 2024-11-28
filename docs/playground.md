---
layout: default
title: Color Playground
---

# Color Playground

Experiment with Color Palette PHP's color manipulation features in this interactive playground. Try out different color spaces, manipulations, and see the results in real-time.

<div id="color-playground">
    <div class="playground-section">
        <h2>Color Input</h2>
        <div class="input-group">
            <label for="color-input">Select a Color:</label>
            <input type="color" id="color-input" value="#2563eb">
        </div>
    </div>

    <div class="playground-section">
        <h2>Color Information</h2>
        <div id="color-info">
            <div class="info-item">
                <strong>Hex:</strong>
                <span id="hex-value">#2563eb</span>
            </div>
            <div class="info-item">
                <strong>RGB:</strong>
                <span id="rgb-value">rgb(37, 99, 235)</span>
            </div>
            <div class="info-item">
                <strong>HSL:</strong>
                <span id="hsl-value">hsl(220, 83%, 53%)</span>
            </div>
            <div class="info-item">
                <strong>CMYK:</strong>
                <span id="cmyk-value">cmyk(84%, 58%, 0%, 8%)</span>
            </div>
        </div>
    </div>

    <div class="playground-section">
        <h2>Color Manipulation</h2>
        <div class="manipulation-controls">
            <div class="control-group">
                <label for="lightness">Lightness:</label>
                <input type="range" id="lightness" min="-100" max="100" value="0">
            </div>
            <div class="control-group">
                <label for="saturation">Saturation:</label>
                <input type="range" id="saturation" min="-100" max="100" value="0">
            </div>
            <div class="control-group">
                <label for="hue">Hue Rotation:</label>
                <input type="range" id="hue" min="0" max="360" value="0">
            </div>
        </div>
    </div>

    <div class="playground-section">
        <h2>Color Variations</h2>
        <div id="color-variations" class="color-grid">
            <!-- Variations will be added here by JavaScript -->
        </div>
    </div>

    <div class="playground-section">
        <h2>Code Example</h2>
        <pre><code id="code-example">
use Farzai\ColorPalette\Color;

$color = Color::fromHex('#2563eb');
$lightened = $color->lighten(20);
$saturated = $color->saturate(20);
$rotated = $color->rotate(45);
        </code></pre>
    </div>
</div>

<style>
#color-playground {
    max-width: 800px;
    margin: 0 auto;
}

.playground-section {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.input-group {
    display: flex;
    align-items: center;
    gap: 1rem;
}

#color-input {
    width: 100px;
    height: 40px;
    padding: 0;
    border: none;
    border-radius: 0.25rem;
}

#color-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.info-item {
    background: var(--code-background);
    padding: 0.75rem;
    border-radius: 0.25rem;
}

.manipulation-controls {
    display: grid;
    gap: 1rem;
}

.control-group {
    display: grid;
    gap: 0.5rem;
}

input[type="range"] {
    width: 100%;
}

.color-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 1rem;
}

.color-swatch {
    aspect-ratio: 1;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('color-input');
    const hexValue = document.getElementById('hex-value');
    const rgbValue = document.getElementById('rgb-value');
    const hslValue = document.getElementById('hsl-value');
    const cmykValue = document.getElementById('cmyk-value');
    const lightnessInput = document.getElementById('lightness');
    const saturationInput = document.getElementById('saturation');
    const hueInput = document.getElementById('hue');
    const colorVariations = document.getElementById('color-variations');
    const codeExample = document.getElementById('code-example');

    function updateColorInfo(color) {
        // Update color information displays
        hexValue.textContent = color;
        
        // Convert hex to RGB
        const r = parseInt(color.slice(1,3), 16);
        const g = parseInt(color.slice(3,5), 16);
        const b = parseInt(color.slice(5,7), 16);
        rgbValue.textContent = `rgb(${r}, ${g}, ${b})`;
        
        // Convert RGB to HSL
        const hsl = rgbToHsl(r, g, b);
        hslValue.textContent = `hsl(${Math.round(hsl[0])}, ${Math.round(hsl[1])}%, ${Math.round(hsl[2])}%)`;
        
        // Convert RGB to CMYK
        const cmyk = rgbToCmyk(r, g, b);
        cmykValue.textContent = `cmyk(${Math.round(cmyk[0])}%, ${Math.round(cmyk[1])}%, ${Math.round(cmyk[2])}%, ${Math.round(cmyk[3])}%)`;
        
        // Update code example
        updateCodeExample(color);
        
        // Generate and display color variations
        generateColorVariations(color);
    }

    function rgbToHsl(r, g, b) {
        r /= 255;
        g /= 255;
        b /= 255;
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;

        if (max === min) {
            h = s = 0;
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }

        return [h * 360, s * 100, l * 100];
    }

    function rgbToCmyk(r, g, b) {
        let c = 1 - (r / 255);
        let m = 1 - (g / 255);
        let y = 1 - (b / 255);
        let k = Math.min(c, m, y);
        
        c = ((c - k) / (1 - k)) * 100;
        m = ((m - k) / (1 - k)) * 100;
        y = ((y - k) / (1 - k)) * 100;
        k = k * 100;
        
        return [c, m, y, k];
    }

    function generateColorVariations(baseColor) {
        colorVariations.innerHTML = '';
        
        // Generate variations
        const variations = [
            { label: 'Original', color: baseColor },
            { label: 'Lighter', color: lightenColor(baseColor, 20) },
            { label: 'Darker', color: lightenColor(baseColor, -20) },
            { label: 'Saturated', color: saturateColor(baseColor, 20) },
            { label: 'Desaturated', color: saturateColor(baseColor, -20) },
            { label: 'Rotated', color: rotateColor(baseColor, 30) }
        ];
        
        variations.forEach(variation => {
            const swatch = document.createElement('div');
            swatch.className = 'color-swatch';
            swatch.style.backgroundColor = variation.color;
            swatch.textContent = variation.label;
            colorVariations.appendChild(swatch);
        });
    }

    function lightenColor(hex, amount) {
        const rgb = hexToRgb(hex);
        const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
        hsl[2] = Math.max(0, Math.min(100, hsl[2] + amount));
        return hslToHex(hsl[0], hsl[1], hsl[2]);
    }

    function saturateColor(hex, amount) {
        const rgb = hexToRgb(hex);
        const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
        hsl[1] = Math.max(0, Math.min(100, hsl[1] + amount));
        return hslToHex(hsl[0], hsl[1], hsl[2]);
    }

    function rotateColor(hex, degrees) {
        const rgb = hexToRgb(hex);
        const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
        hsl[0] = (hsl[0] + degrees) % 360;
        return hslToHex(hsl[0], hsl[1], hsl[2]);
    }

    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    function hslToHex(h, s, l) {
        l /= 100;
        const a = s * Math.min(l, 1 - l) / 100;
        const f = n => {
            const k = (n + h / 30) % 12;
            const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
            return Math.round(255 * color).toString(16).padStart(2, '0');
        };
        return `#${f(0)}${f(8)}${f(4)}`;
    }

    function updateCodeExample(color) {
        codeExample.textContent = `use Farzai\\ColorPalette\\Color;

$color = Color::fromHex('${color}');
$lightened = $color->lighten(${lightnessInput.value});
$saturated = $color->saturate(${saturationInput.value});
$rotated = $color->rotate(${hueInput.value});`;
    }

    // Event listeners
    colorInput.addEventListener('input', (e) => updateColorInfo(e.target.value));
    lightnessInput.addEventListener('input', () => updateColorInfo(colorInput.value));
    saturationInput.addEventListener('input', () => updateColorInfo(colorInput.value));
    hueInput.addEventListener('input', () => updateColorInfo(colorInput.value));

    // Initial update
    updateColorInfo(colorInput.value);
});
</script> 