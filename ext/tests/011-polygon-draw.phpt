--TEST--
Polygon::__construct(), draw(), randomlyRotate(), getters
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
if (!extension_loaded('gd'))              die('skip gd not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Shape\Polygon;
use StanDaniels\ImageGenerator\Shape\Shape;

$canvas  = new Canvas(200, 200);
$color   = new Color(0, 0, 255);
$polygon = new Polygon($canvas, 100, 100, 40, $color, 6);

var_dump($polygon instanceof Polygon);
var_dump($polygon instanceof Shape);
var_dump($polygon->getX()     === 100);
var_dump($polygon->getY()     === 100);
var_dump($polygon->getSize()  === 40);
var_dump($polygon->getSides() === 6);

// draw() must not throw
$polygon->draw();
echo "draw ok\n";

// randomlyRotate() returns $this
$result = $polygon->randomlyRotate();
var_dump($result === $polygon);

// Validation: sides < 3
try {
    new Polygon($canvas, 50, 50, 10, $color, 2);
    echo "NO EXCEPTION\n";
} catch (\InvalidArgumentException $e) {
    echo "InvalidArgumentException: sides\n";
}

// Validation: rotate > 360
try {
    new Polygon($canvas, 50, 50, 10, $color, 4, 361);
    echo "NO EXCEPTION\n";
} catch (\InvalidArgumentException $e) {
    echo "InvalidArgumentException: rotate\n";
}
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
draw ok
bool(true)
InvalidArgumentException: sides
InvalidArgumentException: rotate
