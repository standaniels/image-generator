<?php

namespace StanDaniels\ImageGenerator\Shape;

use InvalidArgumentException;
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;

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
     * @param Canvas $canvas The canvas this shape will be drawn on.
     * @param int $x X coordinate of the center of the shape.
     * @param int $y Y coordinate of the center of the shape.
     * @param int $size The distance to the center each vertex will be.
     * @param Color $color
     */
    public function __construct(Canvas $canvas, int $x, int $y, int $size, Color $color)
    {
        if ($x < 0) {
            throw new InvalidArgumentException("\$x must be at leaste 0, $x given.");
        }
        if ($x > $canvas->getWidth()) {
            throw new InvalidArgumentException("\$x cannot exceed canvas width, $x given.");
        }
        if ($y < 0) {
            throw new InvalidArgumentException("\$y must be at leaste 0, $y given.");
        }
        if ($y > $canvas->getHeight()) {
            throw new InvalidArgumentException("\$y cannot exceed canvas width, $y given.");
        }

        $this->canvas = $canvas;
        $this->x = $x;
        $this->y = $y;
        $this->size = $size;
        $this->color = $color;
    }

    /**
     * Generates a random shape.
     *
     * @param Canvas $canvas The canvas the shape will be drawn on.
     * @param Color|null $color The color of the shape, null for a random color.
     * @return Circle|Polygon
     * @throws \Exception
     */
    public static function random(Canvas $canvas, Color $color = null): Shape
    {
        $x = random_int(0, $canvas->getWidth());
        $y = random_int(0, $canvas->getHeight());
        $geometricAverage = floor(sqrt($canvas->getWidth() * $canvas->getHeight()));
        $size = random_int((int) ($geometricAverage / 8), (int) ($geometricAverage / 4));
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

    /**
     * Draw the shape on its canvas.
     */
    abstract public function draw(): void;

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getSides(): int
    {
        return $this->sides;
    }

    public function getColor(): Color
    {
        return $this->color;
    }
}
