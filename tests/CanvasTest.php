<?php

namespace StanDaniels\ImageGenerator\Tests;

use GdImage;
use InvalidArgumentException;
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;

class CanvasTest extends TestCase
{
    /** @test */
    public function it_can_set_a_background(): void
    {
        Canvas::create(100, 100)
            ->background(new Color(0, 0, 0, 1))
            ->generate($this->targetFile);

        self::assertFileExists($this->targetFile);
    }

    /** @test */
    public function it_increases_the_canvas_to_create_anti_aliasing_effect(): void
    {
        $canvas = Canvas::create(200, 100, 2);

        self::assertEquals(400, $canvas->getWidth());
        self::assertEquals(200, $canvas->getHeight());
    }

    /** @test */
    public function it_can_apply_anti_aliasing(): void
    {
        $canvas = Canvas::create(200, 100, 2);

        $image = $canvas->applyAntiAliasing();

        self::assertEquals(200, imagesx($image));
        self::assertEquals(100, imagesy($image));
        if (strpos(PHP_VERSION, '8.') === 0) {
            self::assertInstanceOf(GdImage::class, $image);
        } else {
            self::assertTrue(
                is_resource($image) && get_resource_type($image) === 'gd',
                'Failed asserting that $image is a resource of type gd.'
            );
        }
    }

    /** @test */
    public function it_throws_an_exception_when_width_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Canvas::create(-1, 100);
    }

    /** @test */
    public function it_throws_an_exception_when_height_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Canvas::create(100, -1);
    }
}
