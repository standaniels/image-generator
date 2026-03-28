--TEST--
Canvas anti-aliasing: internal size is scaled, applyAntiAliasing returns logical size
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
if (!extension_loaded('gd'))              die('skip gd not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Canvas;

// Anti-aliasing factor of 2 → internal canvas is 200×100, logical is 100×50
$c = new Canvas(100, 50, 2.0);
var_dump($c->getWidth());   // logical
var_dump($c->getHeight());  // logical

// Internal GdImage is at 200×100
$gd = $c->getResource();
var_dump(imagesx($gd) === 200);
var_dump(imagesy($gd) === 100);

// applyAntiAliasing() downsamples back to logical size
$small = $c->applyAntiAliasing();
var_dump($small instanceof \GdImage);
var_dump(imagesx($small) === 100);
var_dump(imagesy($small) === 50);
?>
--EXPECT--
int(100)
int(50)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
