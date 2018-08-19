<?php

namespace StanDaniels\ImageGenerator\Tests;

use Mockery as M;
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Shape\Circle;
use StanDaniels\ImageGenerator\Shape\Polygon;
use StanDaniels\ImageGenerator\Shape\Shape;

class ShapeTest extends TestCase
{
    /** @test */
    public function it_can_create_a_random_shape_in_a_random_color()
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('getWidth')
            ->andReturn(100)
            ->times(3);
        $canvas->shouldReceive('getHeight')
            ->andReturn(100)
            ->times(3);
        $shape = Shape::random($canvas);

        $this->assertTrue($shape instanceof Circle || $shape instanceof Polygon);
    }

    /** @test */
    public function it_can_create_a_random_shape_in_a_given_color()
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('getWidth')->andReturn(100);
        $canvas->shouldReceive('getHeight')->andReturn(100);
        $shape = Shape::random($canvas, new Color(255, 128, 100));

        $color = $shape->getColor();

        $this->assertSame(255, $color->getRed());
        $this->assertSame(128, $color->getGreen());
        $this->assertSame(100, $color->getBlue());
    }
}
