<?php

// Create a 100x100 image
$image = imagecreatetruecolor(100, 100);

// Fill it with red color
$red = imagecolorallocate($image, 255, 0, 0);
imagefill($image, 0, 0, $red);

// Save it
if (! is_dir(__DIR__.'/../example/assets')) {
    mkdir(__DIR__.'/../example/assets', 0777, true);
}

imagejpeg($image, __DIR__.'/../example/assets/sample.jpg');
imagedestroy($image);
