--TEST--
Color clone – C struct fields are copied, mutations are independent
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Color;

$original = new Color(109, 175, 53, 0.0);
$clone = clone $original;

// Clone has same initial values
var_dump($clone->getRed());
var_dump($clone->getGreen());
var_dump($clone->getBlue());
var_dump($clone->getAlpha());

// Mutating clone does not affect original
$clone->setAlpha(0.5);
var_dump($clone->getAlpha());   // (int)(0.5 * 127) = 63
var_dump($original->getAlpha()); // still 0
?>
--EXPECT--
int(109)
int(175)
int(53)
int(0)
int(63)
int(0)
