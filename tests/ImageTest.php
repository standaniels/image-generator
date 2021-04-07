<?php

namespace StanDaniels\ImageGenerator\Tests;

use InvalidArgumentException;
use Mockery as M;
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Image;

class ImageTest extends TestCase
{
    /** @test */
    public function it_throws_an_exception_when_image_type_could_not_be_determined(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Image(__DIR__.'/testfiles/not_an_image');
    }

    /** @test */
    public function it_can_be_created_from_a_canvas(): void
    {
        $canvas = new Canvas(100, 100);
        Image::create($canvas, $this->targetFile);
        self::assertFileExists($this->targetFile);
    }

    /** @test */
    public function it_saves_a_canvas_as_a_png(): void
    {
        $canvas = new Canvas(100, 100);
        $image = Image::create($canvas, $this->targetFile);
        self::assertSame('image/png', $image->getMimeType());
        self::assertFileExists($this->targetFile);
        $this->assertImageType($this->targetFile, IMAGETYPE_PNG);
    }

    /** @test */
    public function it_can_be_stored_in_the_default_dir(): void
    {
        $canvas = new Canvas(100, 100);
        $image = Image::create($canvas, $this->targetFile);
        self::assertFileExists($image->getPathname());
    }

    /** @test */
    public function it_can_generate_a_data_uri(): void
    {
        $image = new Image(__DIR__.'/testfiles/test.png');
        $givenDataUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAGElEQVQYlWNkYGD4z0AEYCJG0ahC6ikEAKYXAROlAhdFAAAAAElFTkSuQmCC';
        self::assertEquals($givenDataUri, $image->dataUri());
    }

    /** @test */
    public function it_applies_anti_aliasing_on_creation(): void
    {
        $canvas = M::mock(Canvas::class);
        $canvas->shouldReceive('applyAntiAliasing')
            ->andReturn(imagecreatetruecolor(100, 100))
            ->once();

        Image::create($canvas, $this->targetFile);

        self::assertFileExists($this->targetFile);
    }

    private function assertImageType(string $filePath, $expectedType)
    {
        $expectedType = image_type_to_mime_type($expectedType);
        $type = image_type_to_mime_type(exif_imagetype($filePath));
        self::assertSame($expectedType, $type, "The file `{$filePath}` isn't an `{$expectedType}`, but an `{$type}`");
    }
}
