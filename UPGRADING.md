# Upgrade Guide

## Upgrading from 2.x to 3.0

Version 3.0 changes the palettes that color extraction returns, and changes
one protected method of `AbstractColorExtractor`. The public API is otherwise
unchanged.

> **Behavior change — extracted colors.** k-means now runs from ten seeded,
> count-weighted k-means++ starts and keeps the lowest-error result, instead of
> one start. Extraction is still **deterministic** — the same image always yields
> the same palette — and the palette is now far less sensitive to changes that
> should not matter: in our tests, re-encoded, resized and mirrored copies of a
> photo kept their palette within a CIEDE2000 difference of 2.3 of the
> original's (very small thumbnails on the GD driver drift a little further),
> and the GD and Imagick drivers agree closely. The exact colors differ from
> 2.x for most images, so if you cache or snapshot extracted palettes,
> re-baseline them after upgrading.

**Performance.** Extraction does more clustering work. Measured on a
1600×2292 photo extracting 5 colors (PHP 8.4, Linux), the GD driver went from
about 0.09 s to about 0.4 s and the Imagick driver from about 0.05 s to about
0.1 s; higher color counts cost proportionally more.

**Palette-based and CMYK images.** GD palette images (GIF, PNG-8, 8-bit
grayscale PNG) previously came back as shades of pure blue, or near-black for
small palettes, because the palette index was read as the blue channel. Imagick
CMYK images came back in wrong colors because their C/M/Y values were read as
R/G/B. Both now extract their real colors. A CMYK image that embeds a CMYK ICC
profile, as Photoshop CMYK exports do, is converted through that profile with
the ICC's sRGB profile shipped in `resources/icc`; in our tests its palette
stayed within a CIEDE2000 difference of 1.5 of a full-size conversion. Without
a usable profile, or on an ImageMagick built without lcms, the plain CMYK
formula is used, and its colors can be more than 20 away.

**Extending `AbstractColorExtractor`.** `initializeCentroids()` now receives
the random source to draw from:

```php
// Before
protected function initializeCentroids(array $colors, int $k): array
// After
protected function initializeCentroids(array $colors, int $k, \Random\Randomizer $randomizer): array
```

It is called once per restart, ten times per extraction, with one shared
generator. An override must draw from `$randomizer` rather than seed its own
generator, or every restart starts from the same centroids.

Clustering now assigns colors by squared Euclidean RGB distance directly, so
overriding `calculateColorDistance()` only affects the convergence check, not
cluster assignment.

## Upgrading from 1.x to 2.0

Version 2.0 focuses on a lighter dependency footprint and a higher minimum PHP
version. The public API of color extraction, conversion, manipulation, analysis,
and palette generation is **unchanged**; the breaking changes are limited to the
minimum PHP version and to loading images from remote URLs.

> **Behavior change — extracted colors.** The color-extraction **algorithm and
> public API are unchanged**, but the k-means centroid **seeding** moved off
> PHP's global `mt_rand()` to a locally-seeded
> `\Random\Randomizer(new \Random\Engine\Mt19937($seed))` so that extraction no
> longer mutates your application's global RNG state. As a result, the exact
> colors extracted from a given image **may differ slightly from 1.x** when more
> than one color is requested (`count > 1`). Extraction stays **deterministic
> within 2.0** — the same image always yields the same palette — so if you cache
> or snapshot extracted palettes, re-baseline them after upgrading.

### 1. PHP 8.2 is now required

The minimum supported PHP version is now **8.2** (was 8.1). PHP 8.1 reached its
end of security support on 2025-12-31.

**What to do:** upgrade your runtime to PHP 8.2 or newer.

### 2. The HTTP client is no longer bundled

Previously `symfony/http-client` and `nyholm/psr7` were hard dependencies, so
every install pulled the full Symfony HTTP stack — even when you only extracted
colors from local files. They have been removed from the package's
requirements.

- **Local files and color operations** need no HTTP client and work out of the box.
- **Loading images from a URL** now requires a [PSR-18](https://www.php-fig.org/psr/psr-18/)
  HTTP client (and a PSR-17 factory) to be installed or injected. Any PSR-18
  client is auto-discovered via `php-http/discovery`.

**What to do — only if you load images from URLs:**

```bash
composer require symfony/http-client   # recommended
# or: composer require guzzlehttp/guzzle
```

`symfony/http-client` is recommended because the factory configures it with
`max_redirects = 0`, so the loader re-validates every redirect hop against the
SSRF rules. **Other PSR-18 clients (e.g. Guzzle) may follow redirects internally
by default, which bypasses that per-hop check** — when accepting user-supplied
URLs, prefer `symfony/http-client` or inject a client configured not to follow
redirects (e.g. Guzzle's `['allow_redirects' => false]`).

Nothing else changes — this keeps working once a client is present:

```php
use Farzai\ColorPalette\ImageLoaderFactory;

$loader = (new ImageLoaderFactory)->create();
$image = $loader->load('https://example.com/image.jpg');
```

You can also inject your own client/factory instead of relying on discovery:

```php
$loader = (new ImageLoaderFactory(httpClient: $yourPsr18Client))->create();
```

If you call `load()` with a URL and no PSR-18 client is available, a
`RuntimeException` is thrown explaining how to install or inject one. Loading a
**local file path needs no HTTP client** and always works.

**Direct constructors:** `ImageLoader` and `ImageLoaderFactory` no longer accept
a PSR-17 `StreamFactoryInterface` / `$streamFactory` argument (it was unused). If
you construct either directly, drop that argument and prefer the named arguments
`httpClient:` / `requestFactory:` / `httpConfig:`. Both the client and request
factory are now optional and default to `null`.

### 3. Removed `ImageConstants::HTTP_OK`

The unused public constant `Farzai\ColorPalette\Constants\ImageConstants::HTTP_OK`
was removed. It was never referenced by the library. If you referenced it, use
the literal `200` instead.

### 4. `GdImage::__destruct()` removed

`Farzai\ColorPalette\Images\GdImage` no longer defines a `__destruct()` method.
On PHP 8+, `\GdImage` objects are reference-counted and freed automatically, so
the explicit `imagedestroy()` was unnecessary. This is internal cleanup only —
there is nothing to call and no behavior to migrate.

### 5. `ImageInterface` now declares `getResource()`

`Farzai\ColorPalette\Contracts\ImageInterface` now declares
`public function getResource(): mixed`, formalising the accessor the color
extractors already relied on. The bundled `GdImage` and `ImagickImage` already
implement it, so no change is needed for normal use.

**If you have a custom `ImageInterface` implementation**, add the method:

```php
public function getResource(): mixed
{
    return $this->resource; // your \GdImage, \Imagick, or other backend handle
}
```

### 6. `Theme` is now a validated five-role value object

`Theme` previously stored an arbitrary `name => color` map, so its `ThemeInterface`
getters (`getPrimaryColor()` … `getSurfaceColor()`) threw when a role was absent —
including on `ThemeGenerator`'s own output. A `Theme` now **always defines the five
roles** `primary`, `secondary`, `accent`, `background`, `surface`, and the getters
never throw.

- The constructor and `Theme::fromColors()` now **throw `InvalidArgumentException`
  if any of the five roles is missing**. If you built partial themes, populate all
  five roles (or use the new `Theme::fromRoles(...)` / `Theme::fromPalette($palette)`).
- `ThemeGenerator::generate()` **no longer accepts the second `$colorNames`
  argument** (its signature now matches `ThemeGeneratorInterface`). It no longer
  requires `count(colors) === count(names)`; instead it lifts a role-keyed palette
  (e.g. `WebsiteThemeStrategy` output) directly, or derives all five roles from an
  arbitrary palette. Drop the second argument:

```php
// Before: $generator->generate($palette, ['primary', 'secondary', 'accent']);
$theme = (new ThemeGenerator)->generate($palette); // derives all five roles
```

## New (non-breaking)

### A `Driver` enum for image drivers

You can now pass a `Farzai\ColorPalette\Enums\Driver` enum anywhere a driver was
given as a string (`ColorExtractorFactory::make()`, `ImageFactory::fromPath()` /
`createFromPath()`, `ColorPalette::fromImage()`, `ColorPaletteBuilder::withDriver()`):

```php
use Farzai\ColorPalette\Enums\Driver;

$palette = ColorPalette::fromImage('photo.jpg', 5, Driver::Imagick);
```

Plain strings (`'gd'` / `'imagick'`) keep working — this is additive.
