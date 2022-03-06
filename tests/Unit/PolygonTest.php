<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use InvalidArgumentException;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Polygon;
use Tests\TestCase;

class PolygonTest extends TestCase
{
    public function test_can_create_a_polygon(): void
    {
        $color = Color::fromHex('#FF0000')->setOpacity(50);
        $polygon = Polygon::new(0, 0, 10, 5, 180, $color);

        self::assertSame(
            '#ff0000',
            $polygon->getAttribute('fill'),
            'Failed to assert that the fill color is red.',
        );
        self::assertSame(
            '0.5',
            $polygon->getAttribute('fill-opacity'),

            'Failed to assert that the fill opacity is 0.5',
        );
        self::assertCount(
            5,
            $polygon->getPoints(),
            'Failed to assert that the polygon has 5 sides.',
        );
    }

    /**
     * @dataProvider invalidParametersProvider
     */
    public function test_parameters_are_validated(): void
    {
        try {
            Polygon::new(...func_get_args());
        } catch (Exception $e) {
            self::assertInstanceOf(InvalidArgumentException::class, $e);

            return;
        }
        self::fail('Expected exception not thrown');
    }

    public function invalidParametersProvider(): array
    {
        return [
            'negative_x' => [-1, 0, 1, 3, 0, Color::fromHex('#000000')],
            'negative_y' => [0, -1, 1, 3, 0, Color::fromHex('#000000')],
            '0_size' => [0, 0, 0, 3, 0, Color::fromHex('#000000')],
            'less_than_3_sides' => [0, 0, 1, 2, 0, Color::fromHex('#000000')],
            'less_than_0_rotation' => [0, 0, 1, 3, -1, Color::fromHex('#000000')],
            'more_than_361_rotation' => [0, 0, 1, 3, 361, Color::fromHex('#000000')],
        ];
    }
}
