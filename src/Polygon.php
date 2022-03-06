<?php

declare(strict_types=1);

namespace StanDaniels\ImageGenerator;

use InvalidArgumentException;
use SVG\Nodes\Shapes\SVGPolygon;

final class Polygon
{
    private function __construct()
    {
    }

    /**
     * @param  int  $x  X coordinate of the center of the polygon.
     * @param  int  $y  Y coordinate of the center of the polygon.
     * @param  int  $size  The distance to the center each vertex will be.
     * @param  int  $sides  How many sides this polygon should have. Must be 3 or greater.
     * @param  int  $rotate  The rotation of the polygon, any value between 0 and 360.
     * @param  Color  $color  Fill color
     * @return SVGPolygon
     */
    public static function new(int $x, int $y, int $size, int $sides, int $rotate, Color $color): SVGPolygon
    {
        if ($x < 0) {
            throw new InvalidArgumentException('X coordinate must be 0 or greater.');
        }

        if ($y < 0) {
            throw new InvalidArgumentException('Y coordinate must be 0 or greater.');
        }

        if ($size < 1) {
            throw new InvalidArgumentException('Size must be greater than 0.');
        }

        if ($sides < 3) {
            throw new InvalidArgumentException('Sides must be greater than 2.');
        }

        if ($rotate < 0 || $rotate > 360) {
            throw new InvalidArgumentException('Rotation must be between 0 and 360.');
        }

        // Calculate the coordinates of the vertices using polar coordinates.
        $points = [];
        for ($i = 0; $i < $sides; $i++) {
            $angle = deg2rad($rotate + 360 / $sides * $i);
            $points[] = [
                $x + $size * cos($angle),
                $y + $size * sin($angle),
            ];
        }

        $polygon = new SVGPolygon($points);
        $polygon->setAttribute('fill', $color->hexadecimal());
        $polygon->setAttribute('fill-opacity', (string) $color->opacity());

        return $polygon;
    }
}
