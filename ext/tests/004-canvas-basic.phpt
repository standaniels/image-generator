--TEST--
Canvas::__construct() and getters
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
if (!extension_loaded('gd'))              die('skip gd not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Canvas;

$c = new Canvas(200, 100);
var_dump($c->getWidth());
var_dump($c->getHeight());
var_dump($c->getResource() instanceof \GdImage);

// Factory method
$c2 = Canvas::create(400, 300);
var_dump($c2 instanceof Canvas);
var_dump($c2->getWidth());
var_dump($c2->getHeight());
?>
--EXPECT--
int(200)
int(100)
bool(true)
bool(true)
int(400)
int(300)
