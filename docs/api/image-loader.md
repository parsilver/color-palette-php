# ImageLoader Class API Reference

The `ImageLoader` class is responsible for loading and preparing images for color extraction. It supports multiple image formats and sources.

## Class Synopsis

```php
namespace Farzai\ColorPalette;

class ImageLoader
{
    // Constructor
    public function __construct(?string $driver = null)
    
    // Loading Methods
    public function load(string $source): ImageInterface
    public function loadFromPath(string $path): ImageInterface
    public function loadFromUrl(string $url): ImageInterface
    public function loadFromString(string $data): ImageInterface
    public function loadFromResource($resource): ImageInterface
    
    // Configuration
    public function setDriver(string $driver): self
    public function getDriver(): string
    
    // Validation
    public function supports(string $source): bool
    public function validateSource(string $source): void
}
```

## Constructor

### __construct()

Creates a new ImageLoader instance with an optional driver specification.

```php
public function __construct(?string $driver = null)
```

#### Parameters
- `$driver` (?string): The image processing driver to use ('gd' or 'imagick')

#### Example
```php
// Use default driver (GD if available, otherwise Imagick)
$loader = new ImageLoader();

// Specify GD driver
$loader = new ImageLoader('gd');

// Specify Imagick driver
$loader = new ImageLoader('imagick');
```

## Loading Methods

### load()

Loads an image from various sources (file path, URL, or binary data).

```php
public function load(string $source): ImageInterface
```

#### Parameters
- `$source` (string): The image source (path, URL, or binary data)

#### Returns
- (ImageInterface): The loaded image

#### Example
```php
// Load from file
$image = $loader->load('/path/to/image.jpg');

// Load from URL
$image = $loader->load('https://example.com/image.jpg');
```

### loadFromPath()

Loads an image from a file path.

```php
public function loadFromPath(string $path): ImageInterface
```

#### Parameters
- `$path` (string): Path to the image file

#### Returns
- (ImageInterface): The loaded image

#### Example
```php
$image = $loader->loadFromPath('/path/to/image.jpg');
```

### loadFromUrl()

Loads an image from a URL.

```php
public function loadFromUrl(string $url): ImageInterface
```

#### Parameters
- `$url` (string): URL of the image

#### Returns
- (ImageInterface): The loaded image

#### Example
```php
$image = $loader->loadFromUrl('https://example.com/image.jpg');
```

### loadFromString()

Loads an image from binary string data.

```php
public function loadFromString(string $data): ImageInterface
```

#### Parameters
- `$data` (string): Binary image data

#### Returns
- (ImageInterface): The loaded image

#### Example
```php
$data = file_get_contents('image.jpg');
$image = $loader->loadFromString($data);
```

### loadFromResource()

Loads an image from a PHP resource.

```php
public function loadFromResource($resource): ImageInterface
```

#### Parameters
- `$resource` (resource): PHP image resource

#### Returns
- (ImageInterface): The loaded image

#### Example
```php
$resource = imagecreatefromjpeg('image.jpg');
$image = $loader->loadFromResource($resource);
```

## Configuration

### setDriver()

Sets the image processing driver.

```php
public function setDriver(string $driver): self
```

#### Parameters
- `$driver` (string): The driver to use ('gd' or 'imagick')

#### Returns
- (self): The ImageLoader instance

#### Example
```php
$loader->setDriver('gd');
```

### getDriver()

Gets the current image processing driver.

```php
public function getDriver(): string
```

#### Returns
- (string): The current driver name

## Validation

### supports()

Checks if a given source is supported.

```php
public function supports(string $source): bool
```

#### Parameters
- `$source` (string): The source to check

#### Returns
- (bool): True if the source is supported

#### Example
```php
if ($loader->supports($source)) {
    $image = $loader->load($source);
}
```

### validateSource()

Validates a source before loading.

```php
public function validateSource(string $source): void
```

#### Parameters
- `$source` (string): The source to validate

#### Throws
- ImageException: If the source is invalid or unsupported

## Error Handling

The ImageLoader class throws various exceptions for different error cases:

```php
use Farzai\ColorPalette\Exceptions\ImageLoadException;
use Farzai\ColorPalette\Exceptions\UnsupportedDriverException;
use Farzai\ColorPalette\Exceptions\InvalidSourceException;

try {
    $image = $loader->load($source);
} catch (ImageLoadException $e) {
    // Handle image loading errors
    echo "Failed to load image: " . $e->getMessage();
} catch (UnsupportedDriverException $e) {
    // Handle driver support errors
    echo "Unsupported driver: " . $e->getMessage();
} catch (InvalidSourceException $e) {
    // Handle invalid source errors
    echo "Invalid source: " . $e->getMessage();
}
```

## Best Practices

1. **Driver Selection**
   ```php
   // Check for Imagick availability
   if (extension_loaded('imagick')) {
       $loader = new ImageLoader('imagick');
   } else {
       $loader = new ImageLoader('gd');
   }
   ```

2. **Memory Management**
   ```php
   // For large images, consider setting memory limit
   ini_set('memory_limit', '256M');
   
   try {
       $image = $loader->load($source);
   } finally {
       // Clean up resources
       if (isset($image)) {
           $image->destroy();
       }
   }
   ```

3. **URL Loading Safety**
   ```php
   // Add timeout for URL loading
   $ctx = stream_context_create([
       'http' => ['timeout' => 5]
   ]);
   
   $data = file_get_contents($url, false, $ctx);
   $image = $loader->loadFromString($data);
   ``` 