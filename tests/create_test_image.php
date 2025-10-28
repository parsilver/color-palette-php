<?php

// Create a 100x100 image
$image = imagecreatetruecolor(100, 100);
if ($image === false) {
    throw new RuntimeException('Failed to create image');
}

// Fill it with red color
$red = imagecolorallocate($image, 255, 0, 0);
if ($red === false) {
    throw new RuntimeException('Failed to allocate color');
}
imagefill($image, 0, 0, $red);

// Save it
if (! is_dir(__DIR__.'/../example/assets')) {
    mkdir(__DIR__.'/../example/assets', 0777, true);
}

imagejpeg($image, __DIR__.'/../example/assets/sample.jpg');
imagedestroy($image);
