--TEST--
Image::__construct() – throws for missing file or non-image file
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
if (!extension_loaded('exif'))            die('skip exif not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Image;

// Non-existent file
try {
    new Image('/tmp/this_file_does_not_exist_igext.png');
    echo "NO EXCEPTION\n";
} catch (\InvalidArgumentException $e) {
    echo "InvalidArgumentException: missing file\n";
}

// File that exists but is not an image
$tmp = tempnam(sys_get_temp_dir(), 'igxt');
file_put_contents($tmp, 'not an image');
try {
    new Image($tmp);
    echo "NO EXCEPTION\n";
} catch (\InvalidArgumentException $e) {
    echo "InvalidArgumentException: not an image\n";
} finally {
    @unlink($tmp);
}
?>
--EXPECT--
InvalidArgumentException: missing file
InvalidArgumentException: not an image
