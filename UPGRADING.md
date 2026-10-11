# Upgrade Guide

## Upgrading from 2.x to 3.0

Version 3.0 changes the palettes that color extraction returns. The public
extraction API is unchanged, but `ImagickImage` no longer frees an `\Imagick`
you pass it and its public `__destruct()` method is removed, and a few
extension points for subclasses of the extractors have changed.

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

**Wide-gamut RGB images.** The Imagick driver read every RGB image as sRGB, so
an image that embeds another RGB profile, such as Adobe RGB (1998) from a
camera, Display P3 from an iPhone or Mac, or ProPhoto RGB from a photo editor,
came back duller than it is. Across 18 photos in our tests, the color furthest
off in each palette was a median CIEDE2000 difference of 3 (Display P3) to 5
(Adobe RGB) from the sRGB original's, and over 10 for 3 of them. These images
are now converted through their profile like CMYK ones, which kept every Adobe
RGB and Display P3 palette within 1.6 of the original; re-encoding the original
as JPEG moves it up to 1.7. An image tagged with an sRGB profile other than the
ICC's own, as many sRGB images are, takes the same path and stayed within 3
(median 0.7) of an untagged copy. Without a usable profile, or on an
ImageMagick built without lcms, the pixels are read as sRGB as before. The GD
driver does no color management and reads every image as sRGB.

**Keep `resources/` when you deploy.** The Imagick driver reads
`resources/icc/sRGB2014.icc` for every CMYK image that embeds a CMYK ICC
profile and every RGB image that embeds an RGB one, which includes most camera
and phone photos. If a deploy step or a tool that prunes `vendor/` removes the
file, extraction of those images emits a PHP warning and returns the grayscale
fallback palette; the error itself is reported only through a PSR-3 logger you
pass to the extractor. Profile conversion also needs ImageMagick built with
lcms; without it, RGB images are read as sRGB, as in 2.x, and CMYK images
fall back to the plain formula conversion.

**Transparent images.** Neither driver weighted pixels by their alpha. GD
ignored it, so a fully transparent pixel counted as whatever color was stored
under it. Usually that is black or white, which extraction drops, but an
invisible color could take swatches. Imagick's sampling resize already turned
fully transparent pixels black, but every other pixel counted as opaque: a
nearly invisible background kept its color and could take swatches, and an
entirely transparent image came back as black swatches. Both drivers now skip
fully transparent pixels and count a partially transparent pixel in proportion
to its opacity: a half-transparent pixel counts half as much as an opaque one.
On GD this covers truecolor images (PNG, WebP) and palette images (a GIF's
transparent index, PNG-8 transparency). Both measure opacity on GD's scale of 0
to 127, so a pixel that GD stores as fully transparent, such as a PNG pixel
with an 8-bit alpha of 1, is skipped by Imagick too. An image that is entirely
transparent returns the grayscale fallback palette on both drivers, as an image
with no usable colors already did. Opaque images are not affected. A subclass
that reads the `count` returned by `GdColorExtractor::extractColors()` or
`ImagickColorExtractor::extractColors()` now gets opacity-weighted counts in
1/127ths of a pixel, so an opaque pixel counts 127.

**`ImagickImage` no longer frees the `\Imagick` you pass it.** In 2.x its
destructor called `clear()` on an object it only borrowed, so wrapping one
`\Imagick` twice emptied it when the first wrapper went away, and the next
extraction returned the grayscale fallback palette. The wrapper now borrows the
object, as `GdImage` does, never calls `clear()` on it, and no longer defines a
`__destruct()` method. Images loaded through `ImageFactory::fromPath()` /
`createFromPath()`, `ImageLoader`, `ColorPalette::fromImage()` or
`ColorPaletteBuilder` are still freed when the image is released, because the
wrapper is the only holder. If you construct `new ImagickImage($imagick)`
yourself, keep your own reference to `$imagick`, and relied on the wrapper
going away to free its pixel memory, call `$imagick->clear()` when you are done
with it. A subclass whose destructor calls `parent::__destruct()` must drop
that call; otherwise it throws an `Error` when the wrapper is destroyed.

**Extending `AbstractColorExtractor`.** `initializeCentroids()` now receives
the random source to draw from:

```php
// Before
protected function initializeCentroids(array $colors, int $k): array
// After
protected function initializeCentroids(array $colors, int $k, \Random\Randomizer $randomizer): array
```

A subclass of `AbstractColorExtractor`, `GdColorExtractor` or
`ImagickColorExtractor` that overrides it with the old signature fails with a
fatal error when the class is loaded. A subclass that calls it with the old two
arguments, for example from an overridden `clusterColors()`, does not fail
loudly: `extract()` catches the `ArgumentCountError` and returns the grayscale
fallback palette, and with the default `NullLogger` nothing is reported. Pass a
PSR-3 logger to the extractor while you test the upgrade.

It is called once per restart, up to ten times per extraction, with one shared
generator. An override must draw from `$randomizer` rather than seed its own
generator, or every restart starts from the same centroids. It receives a
0-based list whose colors hold only `r`, `g`, `b` (rounded to integers) and
`count`, and the centroids it returns must have integer channels: a float
centroid can cause a `TypeError`, which `extract()` also turns into the
fallback palette.

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
