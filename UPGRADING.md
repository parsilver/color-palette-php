# Upgrade Guide

## Upgrading from 1.x to 2.0

Version 2.0 focuses on a lighter dependency footprint and a higher minimum PHP
version. Color extraction, conversion, manipulation, analysis, and palette
generation are **unchanged** — the breaking changes are limited to the minimum
PHP version and to loading images from remote URLs.

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

`symfony/http-client` is recommended because the factory configures it to not
follow redirects, so every redirect hop is re-validated against the SSRF rules.

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
`RuntimeException` is thrown explaining how to install or inject one.

### 3. Removed `ImageConstants::HTTP_OK`

The unused public constant `Farzai\ColorPalette\Constants\ImageConstants::HTTP_OK`
was removed. It was never referenced by the library. If you referenced it, use
the literal `200` instead.
