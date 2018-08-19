<?php

namespace StanDaniels\ImageGenerator\Tests;

use Mockery as M;
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Image;

class ImageTest extends TestCase
{
    /** @test */
    public function it_throws_an_exception_when_image_type_could_not_be_determined()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Image(__DIR__ . '/testfiles/not_an_image');
    }

    /** @test */
    public function it_can_be_created_from_a_canvas()
    {
        $canvas = new Canvas(100, 100);
        Image::create($canvas, $this->targetFile);
        $this->assertFileExists($this->targetFile);
    }

    /** @test */
    public function it_saves_a_canvas_as_a_png()
    {
        $canvas = new Canvas(100, 100);
        $image = Image::create($canvas, $this->targetFile);
        $this->assertSame('image/png', $image->getMimeType());
        $this->assertFileExists($this->targetFile);
        $this->assertImageType($this->targetFile, IMAGETYPE_PNG);
    }

    /** @test */
    public function it_can_be_stored_in_the_default_dir()
    {
        $canvas = new Canvas(100, 100);
        $image = Image::create($canvas, $this->targetFile);
        $this->assertFileExists($image->getPathname());
    }

    /** @test */
    public function it_can_generate_a_data_uri()
    {
        $image = new Image(__DIR__ . '/testfiles/test.png');
        $givenDataUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAGElEQVQYlWNkYGD4z0AEYCJG0ahC6ikEAKYXAROlAhdFAAAAAElFTkSuQmCC';
        $this->assertEquals($givenDataUri, $image->dataUri());
    }

    /** @test */
    public function it_applies_anti_aliasing_on_creation()
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('applyAntiAliasing')
            ->andReturn(imagecreatetruecolor(100, 100))
            ->once();

        Image::create($canvas, $this->targetFile);

        $this->assertFileExists($this->targetFile);
    }

    private function assertImageType(string $filePath, $expectedType)
    {
        $expectedType = image_type_to_mime_type($expectedType);
        $type = image_type_to_mime_type(exif_imagetype($filePath));
        $this->assertSame($expectedType, $type, "The file `{$filePath}` isn't an `{$expectedType}`, but an `{$type}`");
    }
}
