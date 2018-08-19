<?php

namespace StanDaniels\ImageGenerator;

use Webmozart\Assert\Assert;

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
        Assert::greaterThan($width, 0);
        Assert::greaterThan($height, 0);

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
     * @param null $path
     * @return Image
     */
    public function generate($path = null)
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

        $realWidth = $this->width / $this->anti_aliasing;
        $realHeight = $this->height / $this->anti_aliasing;

        $resized = imagecreatetruecolor($realWidth, $realHeight);
        if ($resized === false) {
            throw new \RuntimeException('Resizing failed.');
        }

        imagecopyresampled($resized, $this->image, 0, 0, 0, 0, $realWidth, $realHeight, $this->width, $this->height);

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
