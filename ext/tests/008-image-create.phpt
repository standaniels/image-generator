--TEST--
Image::create() – saves PNG and returns Image with correct MIME type
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
if (!extension_loaded('gd'))              die('skip gd not loaded');
if (!extension_loaded('exif'))            die('skip exif not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Image;

$canvas = Canvas::create(20, 20)->background(new Color(0, 128, 0));
$image  = Image::create($canvas);

var_dump($image instanceof Image);
var_dump($image instanceof \SplFileInfo);
var_dump(file_exists($image->getPathname()));
var_dump($image->getMimeType() === 'image/png');

// Data URI starts correctly
$uri = $image->dataUri();
var_dump(str_starts_with($uri, 'data:image/png;base64,'));

// Clean up
@unlink($image->getPathname());
echo "done\n";
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
done
