<?php

namespace StanDaniels\ImageGenerator\Tests;

use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;

class CanvasTest extends TestCase
{
    /** @test */
    public function it_can_set_a_background()
    {
        Canvas::create(100, 100)
            ->background(new Color(0, 0, 0, 1))
            ->generate($this->targetFile);

        $this->assertFileExists($this->targetFile);
    }

    /** @test */
    public function it_increases_the_canvas_to_create_anti_aliasing_effect()
    {
        $canvas = Canvas::create(200, 100, 2);

        $this->assertEquals($canvas->getWidth(), 400);
        $this->assertEquals($canvas->getHeight(), 200);
    }

    /** @test */
    public function it_can_apply_anti_alasing()
    {
        $canvas = Canvas::create(200, 100, 2);

        $image = $canvas->applyAntiAliasing();

        $this->assertEquals(200, imagesx($image));
        $this->assertEquals(100, imagesy($image));
        $this->assertTrue(is_resource($image) && get_resource_type($image) === 'gd', 'Failed asserting that $image is a resource of type gd.');
    }

    /** @test */
    public function it_throws_an_exception_when_width_is_negative()
    {
        $this->expectException(\InvalidArgumentException::class);
        Canvas::create(-1, 100);
    }

    /** @test */
    public function it_throws_an_exception_when_heighth_is_negative()
    {
        $this->expectException(\InvalidArgumentException::class);
        Canvas::create(100, -1);
    }
}
