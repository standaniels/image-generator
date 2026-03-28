--TEST--
Color::__construct() – validation throws InvalidArgumentException
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Color;

$cases = [
    [256, 0, 0, 0.0],
    [-1,  0, 0, 0.0],
    [0, 256, 0, 0.0],
    [0, 0, 256, 0.0],
    [0, 0, 0, 1.1],
    [0, 0, 0, -0.1],
];

foreach ($cases as $args) {
    try {
        new Color(...$args);
        echo "NO EXCEPTION\n";
    } catch (\InvalidArgumentException $e) {
        echo "InvalidArgumentException\n";
    }
}
?>
--EXPECT--
InvalidArgumentException
InvalidArgumentException
InvalidArgumentException
InvalidArgumentException
InvalidArgumentException
InvalidArgumentException
