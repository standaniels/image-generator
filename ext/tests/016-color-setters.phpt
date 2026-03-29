--TEST--
Color setters – update values and validate input
--SKIPIF--
<?php
if (!extension_loaded('image_generator')) die('skip image_generator not loaded');
?>
--FILE--
<?php
use StanDaniels\ImageGenerator\Color;

$c = new Color(10, 20, 30, 0.0);

$c->setRed(200);
$c->setGreen(150);
$c->setBlue(50);
$c->setAlpha(0.5);

var_dump($c->getRed());
var_dump($c->getGreen());
var_dump($c->getBlue());
// alpha = (int)(0.5 * 127) = 63
var_dump($c->getAlpha());

// Validation: out-of-range component
try { $c->setRed(256); echo "NO EXCEPTION\n"; } catch (\InvalidArgumentException $e) { echo "InvalidArgumentException\n"; }
try { $c->setRed(-1);  echo "NO EXCEPTION\n"; } catch (\InvalidArgumentException $e) { echo "InvalidArgumentException\n"; }
try { $c->setGreen(256); echo "NO EXCEPTION\n"; } catch (\InvalidArgumentException $e) { echo "InvalidArgumentException\n"; }
try { $c->setBlue(256);  echo "NO EXCEPTION\n"; } catch (\InvalidArgumentException $e) { echo "InvalidArgumentException\n"; }
try { $c->setAlpha(1.1); echo "NO EXCEPTION\n"; } catch (\InvalidArgumentException $e) { echo "InvalidArgumentException\n"; }
try { $c->setAlpha(-0.1); echo "NO EXCEPTION\n"; } catch (\InvalidArgumentException $e) { echo "InvalidArgumentException\n"; }
?>
--EXPECT--
int(200)
int(150)
int(50)
int(63)
InvalidArgumentException
InvalidArgumentException
InvalidArgumentException
InvalidArgumentException
InvalidArgumentException
InvalidArgumentException
