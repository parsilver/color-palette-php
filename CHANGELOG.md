# Changelog

All notable changes to `color-palette` will be documented in this file.

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
