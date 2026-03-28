--TEST--
Color::allocate() returns a valid GD color identifier
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
if (!extension_loaded('gd'))              die('skip gd not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;

$canvas = new Canvas(10, 10);
$color  = new Color(128, 64, 32, 0.0);
$id     = $color->allocate($canvas);

// GD color identifiers are non-negative integers
var_dump(is_int($id));
var_dump($id >= 0);

// Verify round-trip: imagecolorsforindex should return matching RGB
$rgb = imagecolorsforindex($canvas->getResource(), $id);
var_dump($rgb['red']   === 128);
var_dump($rgb['green'] === 64);
var_dump($rgb['blue']  === 32);
// GD alpha: Color stores (int)(alpha * 127); for alpha=0.0 that's 0
var_dump($rgb['alpha'] === 0);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
