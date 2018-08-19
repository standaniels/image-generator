<?php

namespace StanDaniels\ImageGenerator;

use Webmozart\Assert\Assert;

class Color
{
    /**
     * @var int
     */
    protected $red;

    /**
     * @var int
     */
    protected $green;

    /**
     * @var int
     */
    protected $blue;

    /**
     * @var int int
     */
    protected $alpha;

    /**
     * Color constructor.
     * @param int $red Value between 0 and 255
     * @param int $green Value between 0 and 255
     * @param int $blue Value between 0 and 255
     * @param float $alpha 0 for completely opaque, 1 for completely transparent
     */
    public function __construct(int $red = 0, int $green = 0, int $blue = 0, float $alpha = 0)
    {
        $this->setRed($red);
        $this->setGreen($green);
        $this->setBlue($blue);
        $this->setAlpha($alpha);
    }

    public static function random(float $alpha = null)
    {
        return new static(random_int(0, 255), random_int(0, 255), random_int(0, 255), $alpha ?? (random_int(0, 100) / 100));
    }

    public function allocate(Canvas $canvas): int
    {
        return imagecolorallocatealpha($canvas->getResource(), $this->red, $this->green, $this->blue, $this->alpha);
    }

    public function getRed(): int
    {
        return $this->red;
    }

    /**
     * @param int $red Value between 0 and 255
     */
    public function setRed(int $red): void
    {
        Assert::range($red, 0, 255);
        $this->red = $red;
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    /**
     * @param int $green Value between 0 and 255
     */
    public function setGreen(int $green): void
    {
        Assert::range($green, 0, 255);
        $this->green = $green;
    }

    public function getBlue(): int
    {
        return $this->blue;
    }

    /**
     * @param int $blue Value between 0 and 255
     */
    public function setBlue(int $blue): void
    {
        Assert::range($blue, 0, 255);
        $this->blue = $blue;
    }

    public function getAlpha(): int
    {
        return $this->alpha;
    }

    /**
     * @param float $alpha Value between 0 and 1
     */
    public function setAlpha(float $alpha): void
    {
        Assert::range($alpha, 0, 1);
        $this->alpha = (int) ($alpha * 127);
    }
}
