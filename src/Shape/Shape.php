<?php

namespace StanDaniels\ImageGenerator\Shape;

use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use Webmozart\Assert\Assert;

abstract class Shape
{
    /**
     * @var Canvas
     */
    protected $canvas;

    /**
     * @var int
     */
    protected $x;

    /**
     * @var int
     */
    protected $y;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var int
     */
    protected $sides;

    /**
     * @var Color
     */
    protected $color;

    /**
     * @param Canvas $canvas The canvas this shape will be drawn on
     * @param int $x X coordinate of the center
     * @param int $y Y coordinate of the center
     * @param int $size The distance to the center each vertex will be
     * @param Color $color
     */
    public function __construct(Canvas $canvas, int $x, int $y, int $size, Color $color)
    {
        Assert::range($x, 0, $canvas->getWidth());
        Assert::range($y, 0, $canvas->getHeight());

        $this->canvas = $canvas;
        $this->x = $x;
        $this->y = $y;
        $this->size = $size;
        $this->color = $color;
    }

    public static function random(Canvas $canvas, Color $color = null): Shape
    {
        $x = random_int(0, $canvas->getWidth());
        $y = random_int(0, $canvas->getHeight());
        $geometricAverage = floor(sqrt($canvas->getWidth() * $canvas->getHeight()));
        $size = random_int($geometricAverage / 8, $geometricAverage / 4);
        $color = $color ?? Color::random();

        if (static::class === Circle::class) {
            return new Circle($canvas, $x, $y, $size, $color);
        }

        if (static::class === Polygon::class) {
            return new Polygon($canvas, $x, $y, $size, $color, random_int(3, 8));
        }

        $sides = random_int(2, 8);
        if ($sides === 2) {
            return new Circle($canvas, $x, $y, $size, $color);
        }

        return new Polygon($canvas, $x, $y, $size, $color, $sides);
    }

    abstract public function draw(): void;

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getSides(): int
    {
        return $this->sides;
    }

    /**
     * @return Color
     */
    public function getColor(): Color
    {
        return $this->color;
    }
}
