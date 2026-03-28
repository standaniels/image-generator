<?php

use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Shape\Shape;
use StanDaniels\ImageGenerator\Shape\Circle;
use StanDaniels\ImageGenerator\Shape\Polygon;

echo "Extension loaded: " . (extension_loaded('image_generator') ? 'yes' : 'no') . PHP_EOL;
echo PHP_EOL;

// Basic Color tests
$red = new Color(255, 0, 0);
echo "Color(255,0,0) -> r={$red->getRed()} g={$red->getGreen()} b={$red->getBlue()}" . PHP_EOL;

$random = Color::random(1.0);
echo "Color::random(alpha=1.0) -> r={$random->getRed()} g={$random->getGreen()} b={$random->getBlue()}" . PHP_EOL;
echo PHP_EOL;

// Canvas + shapes -> image
echo "Creating 800x600 canvas (2x antialiasing)..." . PHP_EOL;
$canvas = Canvas::create(800, 600, 2.0);
$canvas->background(new Color(20, 20, 35));

echo "Drawing 30 random shapes..." . PHP_EOL;
for ($i = 0; $i < 30; $i++) {
    Shape::random($canvas)->draw();
}

// A few specific shapes
$canvas->background(new Color(20, 20, 35)); // reset (draws over, not a real reset)

// Explicit circle
$circle = new Circle($canvas, 400, 300, 80, new Color(255, 100, 0));
$circle->draw();

// Explicit polygon (hexagon)
$hex = new Polygon($canvas, 200, 150, 60, new Color(0, 200, 150), 6, 30);
$hex->draw();

// Triangle
$tri = new Polygon($canvas, 600, 450, 70, new Color(200, 50, 200), 3);
$tri->draw();

echo "Generating image..." . PHP_EOL;
$image = $canvas->generate('/app/output/generated.png');

echo "Path:      " . $image->getPathname() . PHP_EOL;
echo "MIME type: " . $image->getMimeType() . PHP_EOL;
echo "File size: " . filesize($image->getPathname()) . " bytes" . PHP_EOL;
$uri = $image->dataUri();
echo "Data URI:  " . substr($uri, 0, 60) . "..." . PHP_EOL;
echo PHP_EOL;

echo "Done." . PHP_EOL;
