--TEST--
Color::__construct() – defaults and explicit values
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Color;

// Default (black, fully opaque)
$c = new Color();
var_dump($c->getRed());
var_dump($c->getGreen());
var_dump($c->getBlue());
var_dump($c->getAlpha());

// Explicit values: red=255, green=128, blue=0, alpha=0.5
$c2 = new Color(255, 128, 0, 0.5);
var_dump($c2->getRed());
var_dump($c2->getGreen());
var_dump($c2->getBlue());
// alpha = (int)(0.5 * 127) = 63
var_dump($c2->getAlpha());
?>
--EXPECT--
int(0)
int(0)
int(0)
int(0)
int(255)
int(128)
int(0)
int(63)
