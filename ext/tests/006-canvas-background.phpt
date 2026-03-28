--TEST--
Canvas::background() – returns self and applies solid fill
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
$color  = new Color(255, 0, 0);   // pure red, fully opaque

$result = $canvas->background($color);

// background() must return $this for fluent chaining
var_dump($result === $canvas);

// Sample the center pixel – should be red (255,0,0)
$gd = $canvas->getResource();
$rgb = imagecolorat($gd, 5, 5);
$r   = ($rgb >> 16) & 0xFF;
$g   = ($rgb >> 8)  & 0xFF;
$b   =  $rgb        & 0xFF;
var_dump($r === 255);
var_dump($g === 0);
var_dump($b === 0);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
