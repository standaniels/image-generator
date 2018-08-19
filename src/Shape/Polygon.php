<?php

namespace StanDaniels\ImageGenerator\Shape;

use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use Webmozart\Assert\Assert;

class Polygon extends Shape
{
    /**
     * @var array
     */
    protected $points;

    /**
     * @var int
     */
    protected $rotate;

    public function __construct(Canvas $canvas, int $x, int $y, int $size, Color $color, int $sides, $rotate = 0)
    {
        parent::__construct($canvas, $x, $y, $size, $color);

        Assert::nullOrRange($rotate, 0, 360 / $sides);

        $this->sides = $sides;
        $this->rotate = $rotate;

        $this->calculatePoints($sides, $rotate);
    }

    protected function calculatePoints($sides, $rotate): void
    {
        $this->points = [];
        for ($i = 0; $i < $sides; $i++) {
            $angle = deg2rad($rotate + 360 / $this->sides * $i);
            $this->points[] = $this->x + $this->size * cos($angle);
            $this->points[] = $this->y + $this->size * sin($angle);
        }
    }

    public function randomlyRotate(): Polygon
    {
        $this->rotate = random_int(0, 360 / $this->sides);
        $this->calculatePoints($this->sides, $this->rotate);

        return $this;
    }

    /**
     * @link https://en.wikipedia.org/wiki/Polar_coordinate_system
     */
    public function draw(): void
    {
        imagefilledpolygon($this->canvas->getResource(), $this->points, $this->sides, $this->color->allocate($this->canvas));
    }
}
