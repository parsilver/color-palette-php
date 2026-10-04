<?php

// Child-process half of the cross-process determinism test. Prints the
// SyntheticPhoto palette for one driver as a JSON array of hex strings.
// usage: php determinism_worker.php <gd|imagick>

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);

require __DIR__.'/../../vendor/autoload.php';

use Farzai\ColorPalette\Tests\Fixtures\SyntheticPhoto;

echo json_encode(SyntheticPhoto::paletteHex($argv[1] ?? 'gd'));
