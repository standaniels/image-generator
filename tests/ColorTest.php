<?php

namespace StanDaniels\ImageGenerator\Tests;

use StanDaniels\ImageGenerator\Color;

class ColorTest extends TestCase
{
    /**
     * @test
     * @dataProvider inValidColorValueSupplier
     */
    public function it_throws_an_acception_when_values_are_out_of_range($red, $green, $blue, $alpha)
    {
        $this->expectException(\InvalidArgumentException::class);
        new Color($red, $green, $blue, $alpha);
    }

    public function inValidColorValueSupplier()
    {
        return [
            [255, 255, 255, -1],
            [255, 255, 255, 2],
            [256, 255, 255, 1],
            [-1, 255, 255, 1],
            [255, 256, 255, 1],
            [255, -1, 255, 1],
            [255, 255, 256, 1],
            [255, 255, -1, 1],
        ];
    }

    /** @test */
    public function it_can_generate_a_random_color()
    {
        $randomColorWithGivenAlpha = Color::random(.5);
        $randomColor = Color::random();

        $this->assertInstanceOf(Color::class, $randomColor);
        $this->assertEquals(63.5, $randomColorWithGivenAlpha->getAlpha(), '', .5);
    }

    /** @test */
    public function it_uses_black_as_the_default_color()
    {
        $color = new Color();
        $this->assertSame(0, $color->getRed());
        $this->assertSame(0, $color->getGreen());
        $this->assertSame(0, $color->getBlue());
        $this->assertSame(0, $color->getAlpha());
    }
}
