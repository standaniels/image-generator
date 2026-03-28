--TEST--
Polygon vertex calculation correctness (3-, 4-, and 6-sided)
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

$canvas = new Canvas(400, 400);
$color  = new Color(0, 0, 0);

// Triangle at (200,200), size=100, no rotation
// Vertex i: angle = deg2rad(0 + 360/3 * i)
// Expected (rounded): [300,200], [150,330], [150,70]  (approx)
$tri = new Polygon($canvas, 200, 200, 100, $color, 3, 0);
$tri->draw(); // exercises calculatePoints internally
echo "triangle drawn\n";

// Square at (100,100), size=50
$sq = new Polygon($canvas, 100, 100, 50, $color, 4, 0);
$sq->draw();
echo "square drawn\n";

// Hexagon
$hex = new Polygon($canvas, 200, 200, 60, $color, 6, 0);
$hex->draw();
echo "hexagon drawn\n";
?>
--EXPECT--
triangle drawn
square drawn
hexagon drawn
