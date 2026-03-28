--TEST--
Circle::__construct(), draw(), getters
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
if (!extension_loaded('gd'))              die('skip gd not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Shape\Circle;
use StanDaniels\ImageGenerator\Shape\Shape;

$canvas = new Canvas(100, 100);
$color  = new Color(255, 0, 0);
$circle = new Circle($canvas, 50, 50, 20, $color);

var_dump($circle instanceof Circle);
var_dump($circle instanceof Shape);
var_dump($circle->getX()     === 50);
var_dump($circle->getY()     === 50);
var_dump($circle->getSize()  === 20);
var_dump($circle->getSides() === 2);
var_dump($circle->getColor() instanceof Color);

// draw() must not throw
$circle->draw();
echo "draw ok\n";

// Out-of-bounds coordinate
try {
    new Circle($canvas, 200, 50, 10, $color);
    echo "NO EXCEPTION\n";
} catch (\InvalidArgumentException $e) {
    echo "InvalidArgumentException: x out of bounds\n";
}
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
draw ok
InvalidArgumentException: x out of bounds
