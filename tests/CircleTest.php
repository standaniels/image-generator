<?php

namespace StanDaniels\ImageGenerator\Tests;

use Mockery as M;
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Shape\Circle;

class CircleTest extends TestCase
{
    /**
     * @test
     * @dataProvider invalidCoordinatesProvider
     */
    public function it_throws_an_exception_if_a_coordinate_is_out_of_bound($w, $h, $x, $y)
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('getWidth')->andReturn($w);
        $canvas->shouldReceive('getHeight')->andReturn($h);
        $color = M::mock(Color::class);

        $this->expectException(\InvalidArgumentException::class);

        new Circle($canvas, $x, $y, 10, $color);
    }

    public function invalidCoordinatesProvider()
    {
        return [
            [
                100, 100, 101, 100,
            ],
            [
                100, 100, 100, 101,
            ],
            [
                100, 100, -1, 100,
            ],
            [
                100, 100, 100, -1,
            ],
        ];
    }

    /** @test */
    public function it_can_generate_a_random_circle()
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('getWidth')->andReturn(100);
        $canvas->shouldReceive('getHeight')->andReturn(100);

        $circle = Circle::random($canvas);

        $this->assertInstanceOf(Circle::class, $circle);
    }

    /** @test */
    public function it_can_be_drawn_on_a_canvas()
    {
        $canvas = new Canvas(100, 100);

        Circle::random($canvas)->draw();
        $canvas->generate($this->targetFile);

        $this->assertFileExists($this->targetFile);
    }

    /** @test */
    public function it_can_return_the_properties()
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('getWidth')->andReturn(100);
        $canvas->shouldReceive('getHeight')->andReturn(100);
        $color = new Color(1, 2, 3, .5);
        $circle = new Circle($canvas, 100, 50, 10, $color);

        $this->assertSame($color, $circle->getColor());
        $this->assertSame(100, $circle->getX());
        $this->assertSame(50, $circle->getY());
        $this->assertSame(10, $circle->getSize());
        $this->assertSame(2, $circle->getSides());
    }
}
