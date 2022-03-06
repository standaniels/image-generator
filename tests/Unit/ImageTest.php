<?php

declare(strict_types=1);

namespace Tests\Unit;

use Imagick;
use Mockery;
use StanDaniels\ImageGenerator\Image;
use SVG\SVG;
use Tests\TestCase;

class ImageTest extends TestCase
{
    public function test_it_can_save_an_svg_as_a_png(): void
    {
        $svg = Mockery::spy(SVG::class);
        $svg->allows('toXmlString')->andReturn('<svg></svg>');

        $imagick = Mockery::spy(Imagick::class);

        $image = new Image($svg, $imagick);
        $image->savePng('my-image.png');

        $imagick->shouldHaveReceived('readImageBlob')->with('<svg></svg>');
        $imagick->shouldHaveReceived('setImageFormat')->with('png');
        $imagick->shouldHaveReceived('writeImage')->with('my-image.png');
    }

    public function test_it_returns_the_svg_instance(): void
    {
        $svg = new Svg();

        $image = new Image($svg, Mockery::mock(Imagick::class));

        $this->assertSame($svg, $image->svg());
    }
}
