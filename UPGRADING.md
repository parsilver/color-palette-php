# Upgrade Guide

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
