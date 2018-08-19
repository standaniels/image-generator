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

    /**
     * @param Canvas $canvas The canvas this shape will be drawn on.
     * @param int $x X coordinate of the center of the polygon.
     * @param int $y Y coordinate of the center of the polygon.
     * @param int $size The distance to the center each vertex will be.
     * @param Color $color
     * @param int $sides How many sides this polygon should have. Must be 3 or greater.
     * @param int $rotate The rotation of the polygon, any value between 0 and 360 / $sides.
     */
    public function __construct(Canvas $canvas, int $x, int $y, int $size, Color $color, int $sides, int $rotate = 0)
    {
        parent::__construct($canvas, $x, $y, $size, $color);

        Assert::range($rotate, 0, 360 / $sides);
        Assert::greaterThan($sides, 2);

        $this->sides = $sides;
        $this->rotate = $rotate;

        $this->calculatePoints($sides, $rotate);
    }

    /**
     * Calculates the coordinates of the vertices using polar coordinates.
     *
     * @param int $sides
     * @param int $rotate
     */
    protected function calculatePoints(int $sides, int $rotate): void
    {
        $this->points = [];
        for ($i = 0; $i < $sides; $i++) {
            $angle = deg2rad($rotate + 360 / $this->sides * $i);
            $this->points[] = $this->x + $this->size * cos($angle);
            $this->points[] = $this->y + $this->size * sin($angle);
        }
    }

    /**
     * Randomly rotate the polygon.
     *
     * @return Polygon
     * @throws \Exception
     */
    public function randomlyRotate(): Polygon
    {
        $this->rotate = random_int(0, 360 / $this->sides);
        $this->calculatePoints($this->sides, $this->rotate);

        return $this;
    }

    public function draw(): void
    {
        imagefilledpolygon($this->canvas->getResource(), $this->points, $this->sides, $this->color->allocate($this->canvas));
    }
}
