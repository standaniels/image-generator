<?php

namespace StanDaniels\ImageGenerator;

use InvalidArgumentException;

class Canvas
{
    /**
     * @var resource A gd resource.
     */
    protected $image;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var float
     */
    protected $anti_aliasing;

    /**
     * @var float|int
     */
    protected $area;

    /**
     * @param int $width Any value greater than 0.
     * @param int $height Any value greater than 0.
     * @param float $antiAliasing The factor by which the canvas size will be increased in order to apply anti-aliasing.
     *
     * @see Canvas::applyAntiAliasing()
     */
    public function __construct(int $width, int $height, float $antiAliasing = 1)
    {
        if ($width <= 0) {
            throw new InvalidArgumentException("\$width must be greater than 0, $width given.");
        }
        if ($height <= 0) {
            throw new InvalidArgumentException("\$height must be greater than 0, $height given.");
        }

        if ($antiAliasing > 1) {
            $width = (int) round($width * $antiAliasing);
            $height = (int) round($height * $antiAliasing);
            $this->anti_aliasing = $antiAliasing;
        }

        $this->width = $width;
        $this->height = $height;
        $this->image = imagecreatetruecolor($width, $height);
        $this->area = $width * $height;
    }

    /**
     * This is an alias for the constructor that allows better fluent syntax.
     *
     * @param int $width Any value greater than 0.
     * @param int $height Any value greater than 0.
     * @param float $antiAliasing The factor by which the canvas size will be increased in order to apply anti-aliasing.
     *
     * @see Canvas::applyAntiAliasing()
     *
     * @return static
     */
    public static function create(int $width, int $height, float $antiAliasing = 1)
    {
        return new static($width, $height, $antiAliasing);
    }

    /**
     * Fills the canvas with a rectangle in the given color.
     *
     * @param Color $color
     * @return Canvas
     */
    public function background(Color $color): self
    {
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $color->allocate($this));

        return $this;
    }

    /**
     * Generates an image based on this canvas.
     *
     * @param string|null $path If null, the image will be written to the directory used for temporary files.
     * @return Image
     */
    public function generate(string $path = null)
    {
        return Image::create($this, $path);
    }

    /**
     * If the anti aliasing value is greater than 1, then the size of the canvas
     * was multiplied by this amount. When an image is generated,
     * the canvas will be resampled and resized to its intended size.
     *
     * @return resource A gd resource of the resampled image.
     */
    public function applyAntiAliasing()
    {
        if ($this->anti_aliasing <= 1) {
            return $this->image;
        }

        $intendedWidth = (int) ($this->width / $this->anti_aliasing);
        $intendedHeight = (int) ($this->height / $this->anti_aliasing);

        $resized = imagecreatetruecolor($intendedWidth, $intendedHeight);
        if ($resized === false) {
            throw new \RuntimeException('Resizing failed.');
        }

        imagecopyresampled($resized, $this->image, 0, 0, 0, 0, $intendedWidth, $intendedHeight, $this->width, $this->height);

        return $resized;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return resource A gd resource.
     */
    public function getResource()
    {
        return $this->image;
    }
}
