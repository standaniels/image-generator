<?php

namespace StanDaniels\ImageGenerator\Tests;

use InvalidArgumentException;
use Mockery as M;
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Shape\Polygon;

class PolygonTest extends TestCase
{
    /**
     * @test
     * @dataProvider pointsProvider
     * @param $sides
     * @param $expected
     */
    public function it_can_calculate_the_points_of_all_the_vertices($sides, $expected): void
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('getWidth')->andReturn(100);
        $canvas->shouldReceive('getHeight')->andReturn(100);
        $color = M::mock(Color::class);

        $polygon = new TestPolygon($canvas, 0, 0, 10, $color, $sides);

        self::assertInstanceOf(Polygon::class, $polygon);
        self::assertGreaterThanOrEqual(1, $polygon->points());
        foreach ($polygon->points() as $key => $value) {
            self::assertEqualsWithDelta($expected[$key], $value, 0.1);
        }
    }

    public function pointsProvider(): array
    {
        return [
            [
                3,
                [
                    10,
                    0,
                    -5,
                    8.6602540378444,
                    -5,
                    -8.6602540378444,
                ],
            ],
            [
                4,
                [
                    10,
                    0,
                    6.1232339957368E-16,
                    10,
                    -10,
                    1.2246467991474E-15,
                    -1.836970198721E-15,
                    -10,
                ],
            ],
            [
                5,
                [
                    10,
                    0,
                    3.0901699437495,
                    9.5105651629515,
                    -8.0901699437495,
                    5.8778525229247,
                    -8.0901699437495,
                    -5.8778525229247,
                    3.0901699437495,
                    -9.5105651629515,
                ],
            ],
            [
                6,
                [
                    10,
                    0,
                    5,
                    8.6602540378444,
                    -5,
                    8.6602540378444,
                    -10,
                    1.2246467991474E-15,
                    -5,
                    -8.6602540378444,
                    5,
                    -8.6602540378444,
                ],
            ],
            [
                7,
                [
                    10,
                    0,
                    6.2348980185873,
                    7.8183148246803,
                    -2.2252093395631,
                    9.7492791218182,
                    -9.0096886790242,
                    4.3388373911756,
                    -9.0096886790242,
                    -4.3388373911756,
                    -2.2252093395631,
                    -9.7492791218182,
                    6.2348980185873,
                    -7.8183148246803,
                ],
            ],
            [
                8,
                [
                    10,
                    0,
                    7.0710678118655,
                    7.0710678118655,
                    6.1232339957368E-16,
                    10,
                    -7.0710678118655,
                    7.0710678118655,
                    -10,
                    1.2246467991474E-15,
                    -7.0710678118655,
                    -7.0710678118655,
                    -1.836970198721E-15,
                    -10,
                    7.0710678118655,
                    -7.0710678118655,
                ],
            ],
        ];
    }

    /** @test */
    public function it_can_generate_a_random_polygon(): void
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('getWidth')->andReturn(100);
        $canvas->shouldReceive('getHeight')->andReturn(100);

        $polygon = Polygon::random($canvas);

        self::assertInstanceOf(Polygon::class, $polygon);
    }

    /** @test */
    public function it_can_be_drawn_on_a_canvas(): void
    {
        $canvas = new Canvas(100, 100);

        Polygon::random($canvas)->draw();
        $canvas->generate($this->targetFile);

        self::assertFileExists($this->targetFile);
    }

    /** @test */
    public function it_can_return_the_properties(): void
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('getWidth')->andReturn(100);
        $canvas->shouldReceive('getHeight')->andReturn(100);
        $color = M::mock(Color::class);
        $color->shouldReceive('getRed')->andReturn(64);
        $color->shouldReceive('getGreen')->andReturn(64);
        $color->shouldReceive('getBlue')->andReturn(64);
        $color->shouldReceive('getAlpha')->andReturn(127);
        $polygon = new Polygon($canvas, 100, 50, 10, $color, 8);

        self::assertSame($color, $polygon->getColor());
        self::assertSame(100, $polygon->getX());
        self::assertSame(50, $polygon->getY());
        self::assertSame(10, $polygon->getSize());
        self::assertSame(8, $polygon->getSides());
    }

    /** @test */
    public function it_cannot_exist_with_less_than_three_sides(): void
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('getWidth')->andReturn(100);
        $canvas->shouldReceive('getHeight')->andReturn(100);
        $color = M::mock(Color::class);
        $color->shouldReceive('getRed')->andReturn(64);
        $color->shouldReceive('getGreen')->andReturn(64);
        $color->shouldReceive('getBlue')->andReturn(64);
        $color->shouldReceive('getAlpha')->andReturn(127);

        $this->expectException(InvalidArgumentException::class);

        new Polygon($canvas, 100, 50, 10, Color::random(), 2);
    }
}

class TestPolygon extends Polygon
{
    public function points(): array
    {
        return $this->points;
    }
}
