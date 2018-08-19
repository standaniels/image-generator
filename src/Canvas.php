<?php

namespace StanDaniels\ImageGenerator;

use Webmozart\Assert\Assert;

class Canvas
{
    protected $image;

    protected $width;

    protected $height;

    protected $anti_aliasing;

    protected $area;

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

    public static function create(int $width, int $height, float $antiAliasing = 1)
    {
        return new static($width, $height, $antiAliasing);
    }

    public function background(Color $color): self
    {
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $color->allocate($this));

        return $this;
    }

    public function generate($path = null): Image
    {
        return Image::create($this, $path);
    }

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

    public function getResource()
    {
        return $this->image;
    }
}
