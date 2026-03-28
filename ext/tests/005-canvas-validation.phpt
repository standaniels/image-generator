--TEST--
Canvas::__construct() – throws for non-positive dimensions
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
if (!extension_loaded('gd'))              die('skip gd not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Canvas;

try {
    new Canvas(0, 100);
    echo "NO EXCEPTION\n";
} catch (\InvalidArgumentException $e) {
    echo "InvalidArgumentException: width\n";
}

try {
    new Canvas(100, 0);
    echo "NO EXCEPTION\n";
} catch (\InvalidArgumentException $e) {
    echo "InvalidArgumentException: height\n";
}

try {
    new Canvas(-1, 100);
    echo "NO EXCEPTION\n";
} catch (\InvalidArgumentException $e) {
    echo "InvalidArgumentException: neg width\n";
}
?>
--EXPECT--
InvalidArgumentException: width
InvalidArgumentException: height
InvalidArgumentException: neg width
