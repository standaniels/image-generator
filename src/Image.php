<?php

namespace StanDaniels\ImageGenerator;

use Webmozart\Assert\Assert;

class Image extends \SplFileInfo
{
    /**
     * @var string
     */
    protected $mime_type;

    public function __construct(string $path)
    {
        Assert::fileExists($path);
        parent::__construct($path);
        $imageType = exif_imagetype($path);
        if ($imageType === false) {
            throw new \InvalidArgumentException('Could not determine image type.');
        }
        $this->mime_type = image_type_to_mime_type($imageType);
    }

    /**
     * @param Canvas $canvas
     * @param string|null $path If null, the image will be written to the temporary files folder.
     * @return Image
     */
    public static function create(Canvas $canvas, string $path = null)
    {
        if (null === $path) {
            $path = tempnam(sys_get_temp_dir(), 'img');
            if ($path === false) {
                throw new \RuntimeException('Failed to create temporary file.');
            }
        }

        $image = $canvas->applyAntiAliasing();
        if (imagepng($image, $path) === false) {
            throw new \RuntimeException('Failed to save image.');
        }

        imagedestroy($image);

        return new static($path);
    }

    public function dataUri(): string
    {
        return "data:{$this->getMimeType()};base64," . base64_encode($this->contents());
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    protected function contents(): string
    {
        return file_get_contents($this->getPathname());
    }
}
