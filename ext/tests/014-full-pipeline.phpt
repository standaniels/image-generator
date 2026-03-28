--TEST--
Full pipeline: create canvas, draw shapes, save image, read data URI
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
use StanDaniels\ImageGenerator\Shape\Shape;
use StanDaniels\ImageGenerator\Shape\Circle;
use StanDaniels\ImageGenerator\Shape\Polygon;

$canvas = Canvas::create(100, 100, 2.0)
    ->background(new Color(200, 200, 200));

// Draw several random shapes
for ($i = 0; $i < 5; $i++) {
    Shape::random($canvas)->draw();
}

// Also draw explicit shapes
$circle  = new Circle($canvas, 50, 50, 15, new Color(255, 0, 0));
$circle->draw();

$polygon = new Polygon($canvas, 50, 50, 20, new Color(0, 0, 255), 5);
$polygon->randomlyRotate()->draw();

// Save to a temp file and verify
$image = Image::create($canvas);

var_dump($image instanceof Image);
var_dump($image instanceof \SplFileInfo);
var_dump(filesize($image->getPathname()) > 0);
var_dump($image->getMimeType() === 'image/png');

$uri = $image->dataUri();
var_dump(str_starts_with($uri, 'data:image/png;base64,'));
// Base64 part should decode to a valid PNG
$b64  = substr($uri, strlen('data:image/png;base64,'));
$data = base64_decode($b64);
var_dump(substr($data, 0, 4) === "\x89PNG");

@unlink($image->getPathname());
echo "done\n";
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
done
