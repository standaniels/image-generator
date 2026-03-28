--TEST--
Shape::random() – returns Circle or Polygon; Circle::random() always returns Circle
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
if (!extension_loaded('gd'))              die('skip gd not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Shape\Shape;
use StanDaniels\ImageGenerator\Shape\Circle;
use StanDaniels\ImageGenerator\Shape\Polygon;

$canvas = new Canvas(200, 200);

// Shape::random() must return Circle or Polygon
for ($i = 0; $i < 10; $i++) {
    $shape = Shape::random($canvas);
    $ok = ($shape instanceof Circle) || ($shape instanceof Polygon);
    if (!$ok) {
        echo "UNEXPECTED TYPE: " . get_class($shape) . "\n";
    }
}
echo "shape random ok\n";

// Circle::random() must always return a Circle
for ($i = 0; $i < 5; $i++) {
    $c = Circle::random($canvas);
    var_dump($c instanceof Circle);
}

// Polygon::random() must always return a Polygon
for ($i = 0; $i < 5; $i++) {
    $p = Polygon::random($canvas);
    var_dump($p instanceof Polygon);
}

// Provided color is used
$color = new Color(10, 20, 30);
$shape = Shape::random($canvas, $color);
var_dump($shape->getColor()->getRed()   === 10);
var_dump($shape->getColor()->getGreen() === 20);
var_dump($shape->getColor()->getBlue()  === 30);
?>
--EXPECT--
shape random ok
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
