# Changelog

All notable changes to `color-palette` will be documented in this file.

## 3.0.0 - 2026-10-11

A major release that makes color extraction stable and correct. Each extraction now keeps the best of up to ten seeded, count-weighted k-means++ runs instead of relying on a single random start, so re-encoded or resized copies of an image usually come back with nearly the same palette. Both drivers now weight pixels by opacity, GD reads palette images (GIF, PNG-8) correctly, and the Imagick driver converts CMYK and wide-gamut RGB images through their embedded ICC profile. The public extraction API is unchanged, but palettes differ from 2.x, a few extension points for subclasses have changed, and `ImagickImage` no longer frees an `\Imagick` you pass it.

### Breaking Changes

- **Extracted palettes differ from 2.x for most images.** Extraction is still deterministic, but the colors are not the ones 2.x returned. If you cache, store or snapshot extracted palettes, re-baseline them after upgrading. The new clustering changes the palette of most images; the fixes below change some kinds of image much more: GD palette images (GIF, PNG-8, 8-bit grayscale PNG), images with transparent or semi-transparent pixels on either driver, and on Imagick, CMYK images, ICC-tagged RGB images and images in any other colorspace that is neither sRGB nor gray (for example Lab or linear RGB), which are now converted to sRGB instead of having their raw channels read as RGB. On Imagick, an image tagged with an sRGB profile other than the ICC's own, as many sRGB images are, now takes the same shrink-and-convert path; the extra shrink step moved its palette up to 3 dE00 (median 0.7) from an untagged copy.
  
- **On Imagick, palettes of profile-tagged images depend on the ImageMagick build.** The same image always yields the same palette on the same PHP and ImageMagick build. Whether a CMYK or ICC-tagged RGB image goes through its profile or falls back depends on ImageMagick having the lcms delegate, so if your hosts or containers differ, re-baseline cached palettes per environment.
  
- **`initializeCentroids()` has a new signature.** It now receives the random source to draw from:
  
  ```php
  // 2.x
  protected function initializeCentroids(array $colors, int $k): array
  // 3.0
  protected function initializeCentroids(array $colors, int $k, \Random\Randomizer $randomizer): array
  
  ```
  A subclass of `AbstractColorExtractor`, `GdColorExtractor` or `ImagickColorExtractor` that overrides it with the old signature fails with a fatal error when the class is loaded. A subclass that calls it with the old two arguments, for example from an overridden `clusterColors()`, does not fail loudly: `extract()` catches the `ArgumentCountError` and returns the grayscale fallback palette, and with the default `NullLogger` nothing is reported. Pass a PSR-3 logger to the extractor while you test the upgrade.
  
  The method is called once per restart, up to ten times per extraction, with one shared generator: an override must draw from `$randomizer` rather than seed its own generator, or every restart starts from the same centroids. It now receives a 0-based list whose colors hold only `r`, `g`, `b` (rounded to integers) and `count`, and the centroids it returns must have integer channels; float centroids cause a `TypeError` that `extract()` also turns into the fallback palette. A library that has to support both majors can declare the third parameter as `?\Random\Randomizer $randomizer = null` and draw from it when it is given.
  
- **`calculateColorDistance()` no longer decides cluster assignment.** Centroid seeding and cluster assignment now use squared Euclidean RGB distance directly. Overriding `calculateColorDistance()` raises no error, but it now only affects the convergence check, so a custom distance metric silently stops shaping the palette.
  
- **The protected `$seed` no longer gives alternative palettes.** It still seeds the generator, but best-of-ten clustering is designed not to depend on the draw, so changing it now has little effect on the result.
  
- **Color counts seen by subclasses are opacity-weighted.** The protected `extractColors()` of `GdColorExtractor` and `ImagickColorExtractor` now returns each color's `count` in 1/127ths: an opaque pixel counts 127, a half-transparent one about half that, and fully transparent pixels are left out. A subclass that reads `count`, for example in an overridden `processColors()`, gets an opacity-weighted count of sampled pixels by dividing by 127 (equal to the sampled-pixel count for opaque images). Clustering uses counts only as relative weights, so the bundled extractors are unaffected.
  
- **`ImagickImage` no longer clears the `\Imagick` it wraps, and `ImagickImage::__destruct()` is removed.** The wrapper now borrows the object it is given, as `GdImage` does (see Fixes). Images loaded through `ImageFactory::fromPath()` / `createFromPath()`, `ImageLoader`, `ColorPalette::fromImage()` or `ColorPaletteBuilder` are still freed when the image is released, because the wrapper is the only holder. If you construct `new ImagickImage($imagick)` yourself, keep your own reference, and relied on the wrapper going away to free its pixel memory, call `$imagick->clear()` when you are done with it. A subclass whose destructor calls `parent::__destruct()` must drop that call; otherwise it throws an `Error` when the wrapper is destroyed.
  

### Packaging and licensing

- **The package now ships a file under a licence other than MIT.** `resources/icc/sRGB2014.icc` is the ICC's sRGB profile, unaltered; the Imagick driver uses it to convert CMYK and wide-gamut RGB images to sRGB. It is distributed under the ICC's terms, which permit redistribution without restriction, not under the package's MIT licence; see `resources/icc/README.md`. `composer.json` still declares MIT, which covers the package's own code.
- **Keep `resources/` when you deploy or prune `vendor/`.** The Imagick driver reads this file for every image that embeds a CMYK or RGB ICC profile, which includes most camera and phone photos. Without it, extraction of those images emits a PHP warning and returns the grayscale fallback palette; the error itself is reported only through a PSR-3 logger you pass to the extractor.
- **Profile conversion needs ImageMagick built with lcms.** Without it, CMYK images fall back to the formula conversion, and RGB images are read as sRGB, as they were in 2.x.

### Fixes

- **Stable palettes ([#53](https://github.com/parsilver/color-palette-php/pull/53)).** Extraction was already repeatable, but the palette depended on a single random draw: across seeds 0-199 the photo from [#13](https://github.com/parsilver/color-palette-php/issues/13) produced 53 (GD) and 64 (Imagick) different palettes, and a re-encoded or upscaled copy could come back with different colors. Extraction now runs up to ten restarts of count-weighted k-means++ (the first centroid chosen by pixel count, each further one by pixel count times squared distance) from one locally seeded generator, and keeps the run with the lowest weighted squared error. Over seeds 0-19 and the shipped seed 42, the #13 photo and ten re-encoded and resized copies of it kept their palettes within a CIEDE2000 (dE00) difference of 2.3 of the original's with both drivers (very small thumbnails on the GD driver drift a little further); on a deliberately ambiguous synthetic image one case still flips. PHP's global RNG is still left untouched, and the draws use 31 bits, so 32-bit and 64-bit PHP builds give the same palette.
- **GD palette images ([#53](https://github.com/parsilver/color-palette-php/pull/53)).** For GIF, PNG-8 and 8-bit grayscale PNG images, `imagecolorat()` returns a palette index rather than an RGB value, and 2.x read that index as the blue channel. These images came back as shades of pure blue, near-black for small palettes, or the grayscale fallback. The index is now resolved through the image's palette; pixels that reference an index the palette does not define (a malformed file) are skipped.
- **CMYK and other non-sRGB images on Imagick ([#53](https://github.com/parsilver/color-palette-php/pull/53), [#55](https://github.com/parsilver/color-palette-php/pull/55)).** Pixel channels were read as R/G/B whatever the image's colorspace, so CMYK images came back in the wrong colors. Images in a colorspace other than sRGB or gray are now converted to sRGB. When a CMYK image embeds a CMYK ICC profile, as Photoshop CMYK exports do, the conversion goes through that profile: the plain CMYK formula missed by up to 23 dE00 on test photos, while the profile path kept 8 photos x 2 profiles within 1.45 dE00 of a full-size conversion. A profile that is corrupt, is not a CMYK profile, or cannot be applied because ImageMagick was built without lcms falls back to the formula.
- **Wide-gamut RGB images on Imagick ([#58](https://github.com/parsilver/color-palette-php/pull/58)).** ImageMagick reports every RGB image as sRGB whatever profile it embeds, so Adobe RGB (1998), Display P3 and ProPhoto RGB images came back duller than they are. Across 18 photos, the color furthest off in each palette was a median 3 (Display P3) to 5 (Adobe RGB) dE00 from the sRGB original's, and over 10 for 3 of them. An RGB image whose profile is not byte-identical to the shipped `sRGB2014.icc` is now converted through that profile, which kept every Adobe RGB and Display P3 palette within 1.6 dE00 of the original; re-encoding the original as JPEG moves it up to 1.7. Without a usable profile, or on an ImageMagick built without lcms, pixels are read as sRGB as before. The GD driver does no color management and reads every image as sRGB.
- **Transparent images ([#56](https://github.com/parsilver/color-palette-php/pull/56), [#57](https://github.com/parsilver/color-palette-php/pull/57)).** Neither driver weighted pixels by their alpha. GD ignored it, so a fully transparent pixel counted as whatever color was stored under it: a PNG with an invisible green under its transparent background got 2 of 5 swatches in that green. Imagick's alpha-weighted resize to its 50x50 sample already turned fully transparent pixels black (and dropped them as too dark), but every other pixel counted as opaque: a nearly invisible background kept its color and could take swatches, and an entirely transparent image came back as `#000000` swatches. Both drivers now skip fully transparent pixels and count a partially transparent pixel in proportion to its opacity, measured on GD's scale of 0 to 127, so they agree on which pixels are invisible. On GD this covers truecolor images and palette transparency (a GIF's transparent index, PNG-8 transparency). An entirely transparent image now returns the grayscale fallback palette on both drivers. Opaque images are not affected by this change: 133 GD and 225 Imagick palettes of opaque images were byte-identical with and without it.
- **`ImagickImage` no longer empties the caller's `\Imagick` ([#54](https://github.com/parsilver/color-palette-php/pull/54)).** Its destructor called `clear()` on an object it only borrowed. A caller that wrapped one `\Imagick` twice had it emptied when the first wrapper went away, so the next `extract()` silently returned the grayscale fallback palette, and any later use of the caller's object failed too.

### Performance

- **Extraction does more clustering work.** Ten restarts make clustering cost grow about tenfold; the assignment loop is now inlined with squared integer distances to win part of that back. Measured when the clustering change landed in #53, on a 1600x2292 photo extracting 5 colors (PHP 8.4, Linux), the GD driver went from about 0.09 s to about 0.4 s and the Imagick driver from about 0.05 s to about 0.1 s; higher color counts cost proportionally more. Extracting a single color, or an image with no more distinct colors than requested, stops after the first run.
- **Color conversion on Imagick runs on a reduced image.** Images that are converted to sRGB (CMYK, ICC-tagged RGB other than the ICC's own sRGB profile, and other non-sRGB colorspaces) are shrunk to at most 200x200 first. On a 48 MP CMYK JPEG, extraction now peaks at 415 MB and takes 1.8 s, against 412 MB and 1.3 s just to decode the file; converting at full size would have peaked at 678 MB and taken 24 s. On a 48 MP Display P3 JPEG the conversion had no measurable cost (0.25 s, 325 MB either way). sRGB images without a profile or with the ICC's own sRGB profile, and grayscale images, skip this step.

### Internal / Tooling

- **Extraction tests that can fail ([#53](https://github.com/parsilver/color-palette-php/pull/53)).** The idempotence tests added for #13 extracted from a single-color image, on which every k-means start gives the same answer, so they also passed against the unfixed code. They are replaced by a deterministic synthetic photo on which k-means is sensitive to its starting point, with tests for a GD golden palette, independence from the extractor instance and from PHP's global RNG, equality across processes, and brightest-first ordering. The basic extraction tests now pin their driver and assert that they do not get the fallback palette.
- **ICC test fixtures built in memory ([#55](https://github.com/parsilver/color-palette-php/pull/55), [#58](https://github.com/parsilver/color-palette-php/pull/58)).** A CMYK profile with dot gain that the formula misreads by up to 64 per channel, and a wide-gamut RGB profile (ROMM primaries, 1.8 gamma), so the profile paths are only satisfied by a real profile conversion.
- **Memory release test ([#54](https://github.com/parsilver/color-palette-php/pull/54)).** A test checks through ImageMagick's resource counters that images created by `ImageFactory` release their pixel cache, where the ImageMagick build reports it.
- `actions/checkout` bumped from 6 to 7 ([#52](https://github.com/parsilver/color-palette-php/pull/52)).
- README notes that the Imagick driver reads CMYK, Adobe RGB and Display P3 images through their embedded ICC profile, and UPGRADING.md has a new "Upgrading from 2.x to 3.0" section ([#53](https://github.com/parsilver/color-palette-php/pull/53), [#55](https://github.com/parsilver/color-palette-php/pull/55), [#57](https://github.com/parsilver/color-palette-php/pull/57), [#58](https://github.com/parsilver/color-palette-php/pull/58), [#59](https://github.com/parsilver/color-palette-php/pull/59)).

Migration notes: [UPGRADING.md](https://github.com/parsilver/color-palette-php/blob/main/UPGRADING.md).

**Full Changelog**: https://github.com/parsilver/color-palette-php/compare/2.0.0...3.0.0

## 2.0.0 - 2026-06-14

A major release that **drops the bundled HTTP stack** for a lighter, PSR-driven dependency footprint, raises the **minimum PHP to 8.2**, makes `Theme` a proper value object, and adds **security hardening** to image loading. A default install now pulls only 5 PSR/discovery packages — no Symfony, no Nyholm — unless you load images from URLs.

See **[UPGRADING.md](UPGRADING.md)** for step-by-step migration.

### ⚠️ Breaking Changes

- **PHP 8.2+ is now required** (was 8.1; 8.1 reached security EOL on 2025-12-31).
  
- **The HTTP client is no longer bundled.** `symfony/http-client` and `nyholm/psr7` are no longer hard dependencies. Local-file extraction and all color operations need **no** HTTP client. **Loading images from a URL** now requires a [PSR-18](https://www.php-fig.org/psr/psr-18/) client (auto-discovered via `php-http/discovery`); install one with `composer require symfony/http-client` (recommended) or any PSR-18 client. A clear `RuntimeException` is thrown if you load a URL without one.
  
  - As part of this, `ImageLoader::__construct()` / `ImageLoaderFactory::__construct()` **no longer accept the `StreamFactoryInterface` / `$streamFactory` argument** (it was unused) and their client/factory arguments are now optional.
  
- **`Theme` is now a validated five-role value object.** It always defines `primary`, `secondary`, `accent`, `background`, `surface`, and its getters never throw. The constructor and `Theme::fromColors()` now throw `InvalidArgumentException` if a role is missing. `ThemeGenerator::generate()` **drops the second `$colorNames` argument** (its signature now matches the interface) and derives/lifts all five roles instead of requiring an exact color/name count.
  
- **`ImageInterface` now declares `getResource(): mixed`.** Custom implementations must add the method (the bundled `GdImage`/`ImagickImage` already have it).
  
- **Removed `ImageConstants::HTTP_OK`** (was unreferenced; use the literal `200`).
  
- **`GdImage::__destruct()` removed** (PHP 8 frees `\GdImage` automatically — internal cleanup only).
  
- **Extracted palettes may differ slightly from 1.x.** The extraction algorithm and public API are unchanged, but k-means centroid **seeding** moved off PHP's global `mt_rand()` to a locally-seeded `\Random\Randomizer(new Mt19937($seed))`. Exact colors can change for the same image when `count > 1`; results remain **deterministic and idempotent within 2.0**. Re-baseline any cached/snapshotted palettes.
  

### 🔒 Security

- **Per-redirect-hop SSRF re-validation** — transport redirects are disabled and every `Location` hop is re-validated against the SSRF rules, closing redirect-based bypasses.
- **Decompression-bomb / pixel-flood protection** — decoded-dimension caps (12000 px/side, 50 MP) reject oversized images before decode on **both** GD and Imagick.
- **Imagick path hardened to GD parity** — now enforces the same size / MIME / dimension validation it previously skipped.
- **k-means seeding no longer mutates PHP's global RNG.**

### ✨ Features

- A **`Driver` enum** (`Farzai\ColorPalette\Enums\Driver`) — pass `Driver::Gd` / `Driver::Imagick` anywhere a driver string was accepted (`make()`, `fromPath()`, `fromImage()`, `withDriver()`); strings keep working.
- `Color::fromHex()` accepts **3-character shorthand** (e.g. `#abc`).
- `ColorPalette` implements **`IteratorAggregate`** (palettes are `foreach`-iterable).
- New **`ColorPaletteBuilder::withDriver()`** and **`Theme::fromRoles()` / `Theme::fromPalette()`**.
- **Spelling-insensitive scheme names** via the consolidated `StrategyRegistry` (previously-valid names keep working).
- Expanded accepted image types (`image/jpg`, `image/bmp`, `image/tiff`).

### 🐛 Fixes

- **Hue normalized to `[0, 360)`** in `toHsl()` / `toHsv()` (fixes `toHsv()`→`fromHsv()` round-trips).
- **`count = 1` no longer throws `DivisionByZeroError`** in the Monochromatic / Shades / Tints strategies.
- **Local-file loading works without an HTTP client** through the loader and `ColorPaletteBuilder` (previously threw on a default install).
- **`ColorPaletteBuilder` no longer silently returns a grayscale palette** when the loader's auto-detected driver differs from the extractor's.
- **`ImagickImage` destructor no longer raises a fatal** when `clear()` fails.
- **`Theme` getters no longer throw** on generated themes, and `ThemeGenerator` no longer corrupts string-keyed palettes.
- Guard k-means clustering against a zero total weight.

### 🧱 Internal / Tooling

- `psr/log` promoted to a direct dependency; `minimum-stability` set to `stable`.
- Single-source color logic (`Color` delegates to `ColorAnalyzer` / `ColorManipulator`); consolidated `StrategyRegistry`; `ImageLoader` implements `ImageLoaderInterface`; corrected interface `@throws`.
- PHPStan (level 6) + Mockery expectation verification wired into CI; slimmer distributed package (examples, assets and dev config are `export-ignore`d).
- Redesigned `example/web-ui` demo (not shipped to installs).

**Full migration guide:** [UPGRADING.md](UPGRADING.md)

## 1.2.1 - 2025-11-27

### What's Changed

* Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/parsilver/color-palette-php/pull/24
* Update symfony/http-client requirement from ^6.4 to ^7.2 by @dependabot[bot] in https://github.com/parsilver/color-palette-php/pull/7
* Update pestphp/pest requirement from ^2.20 to ^3.7 by @dependabot[bot] in https://github.com/parsilver/color-palette-php/pull/9

**Full Changelog**: https://github.com/parsilver/color-palette-php/compare/1.2.0...1.2.1

## 1.2.0 - 2025-10-28

### What's Changed

* Add new palette generation algorithms and ColorPaletteBuilder by @parsilver in https://github.com/parsilver/color-palette-php/pull/21
* Add interactive web UI demo by @parsilver in https://github.com/parsilver/color-palette-php/pull/22
* feat: Add comprehensive HTTP security features by @parsilver in https://github.com/parsilver/color-palette-php/pull/23

**Full Changelog**: https://github.com/parsilver/color-palette-php/compare/1.1.1...1.2.0

## 1.1.0 - 2025-10-20

### What's Changed

* Add missing documentation for ColorExtractorFactory, GdColorExtractor, and ImagickColorExtractor by @parsilver in https://github.com/parsilver/color-palette-php/pull/5
* Fix: Ensure idempotent color extraction (Closes #13) by @parsilver in https://github.com/parsilver/color-palette-php/pull/18
* Fix critical documentation inaccuracies and interface implementations by @parsilver in https://github.com/parsilver/color-palette-php/pull/19
* Improve test coverage by @parsilver in https://github.com/parsilver/color-palette-php/pull/20

**Full Changelog**: https://github.com/parsilver/color-palette-php/compare/1.0.0...1.1.0

## 1.0.0 - 2024-11-28

### What's Changed

* feat: Generate color palette from base color by @parsilver in https://github.com/parsilver/color-palette-php/pull/3
* [ImgBot] Optimize images by @imgbot in https://github.com/parsilver/color-palette-php/pull/4

### New Contributors

* @parsilver made their first contribution in https://github.com/parsilver/color-palette-php/pull/3
* @imgbot made their first contribution in https://github.com/parsilver/color-palette-php/pull/4

**Full Changelog**: https://github.com/parsilver/color-palette-php/compare/0.1.0...1.0.0
