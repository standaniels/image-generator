--TEST--
Color::random() – returns Color with values in range
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Color;

// Without explicit alpha
$c = Color::random();
var_dump($c instanceof Color);
var_dump($c->getRed()   >= 0 && $c->getRed()   <= 255);
var_dump($c->getGreen() >= 0 && $c->getGreen() <= 255);
var_dump($c->getBlue()  >= 0 && $c->getBlue()  <= 255);
var_dump($c->getAlpha() >= 0 && $c->getAlpha() <= 127);

// With explicit alpha = 1.0  → GD alpha = 127
$c2 = Color::random(1.0);
var_dump($c2->getAlpha());

// With explicit alpha = 0.0  → GD alpha = 0
$c3 = Color::random(0.0);
var_dump($c3->getAlpha());
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
int(127)
int(0)
