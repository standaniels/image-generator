<?php

namespace StanDaniels\ImageGenerator\Shape;

use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;

class Circle extends Shape
{
    public function __construct(Canvas $canvas, int $x, int $y, int $size, Color $color)
    {
        parent::__construct($canvas, $x, $y, $size, $color);
        $this->sides = 2;
    }

    public function draw(): void
    {
        $drawn = imagefilledellipse($this->canvas->getResource(), $this->x, $this->y, $this->size, $this->size, $this->color->allocate($this->canvas));
        if ($drawn === false) {
            throw new \RuntimeException('Circle could not be drawn.');
        }
    }
}
