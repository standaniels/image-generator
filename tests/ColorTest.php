<?php

namespace StanDaniels\ImageGenerator\Tests;

use InvalidArgumentException;
use StanDaniels\ImageGenerator\Color;

class ColorTest extends TestCase
{
    /**
     * @test
     * @dataProvider inValidColorValueSupplier
     * @param $red
     * @param $green
     * @param $blue
     * @param $alpha
     */
    public function it_throws_an_exception_when_values_are_out_of_range($red, $green, $blue, $alpha): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Color($red, $green, $blue, $alpha);
    }

    public function inValidColorValueSupplier(): array
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
    public function it_can_generate_a_random_color(): void
    {
        $randomColorWithGivenAlpha = Color::random(.5);
        $randomColor = Color::random();

        self::assertInstanceOf(Color::class, $randomColor);
        self::assertEqualsWithDelta(63.5, $randomColorWithGivenAlpha->getAlpha(), .5);
    }

    /** @test */
    public function it_uses_black_as_the_default_color(): void
    {
        $color = new Color();
        self::assertSame(0, $color->getRed());
        self::assertSame(0, $color->getGreen());
        self::assertSame(0, $color->getBlue());
        self::assertSame(0, $color->getAlpha());
    }
}
