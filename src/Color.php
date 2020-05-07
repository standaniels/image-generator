<?php

namespace StanDaniels\ImageGenerator;

use InvalidArgumentException;

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
     * @param int $red Value between 0 and 255.
     * @param int $green Value between 0 and 255.
     * @param int $blue Value between 0 and 255.
     * @param float $alpha Value between 0 and 1; 0 for completely opaque, 1 for completely transparent.
     */
    public function __construct(int $red = 0, int $green = 0, int $blue = 0, float $alpha = 0)
    {
        $this->setRed($red);
        $this->setGreen($green);
        $this->setBlue($blue);
        $this->setAlpha($alpha);
    }

    /**
     * Creates a random color.
     *
     * @param float|null $alpha Value between 0 and 1; 0 for completely opaque, 1 for completely transparent, null for random.
     *
     * @return Color
     */
    public static function random(float $alpha = null)
    {
        return new static(random_int(0, 255), random_int(0, 255), random_int(0, 255), $alpha ?? (random_int(0, 100) / 100));
    }

    /**
     * Allocate the color on the given canvas.
     *
     * @param Canvas $canvas
     *
     * @return int
     */
    public function allocate(Canvas $canvas): int
    {
        return imagecolorallocatealpha($canvas->getResource(), $this->red, $this->green, $this->blue, $this->alpha);
    }

    public function getRed(): int
    {
        return $this->red;
    }

    /**
     * @param int $red Value between 0 and 255.
     */
    public function setRed(int $red): void
    {
        if ($red < 0 || $red > 255) {
            throw new InvalidArgumentException("\$red be between 0 and 255, $red given.");
        }
        $this->red = $red;
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    /**
     * @param int $green Value between 0 and 255.
     */
    public function setGreen(int $green): void
    {
        if ($green < 0 || $green > 255) {
            throw new InvalidArgumentException("\$green be between 0 and 255, $green given.");
        }
        $this->green = $green;
    }

    public function getBlue(): int
    {
        return $this->blue;
    }

    /**
     * @param int $blue Value between 0 and 255.
     */
    public function setBlue(int $blue): void
    {
        if ($blue < 0 || $blue > 255) {
            throw new InvalidArgumentException("\$blue be between 0 and 255, $blue given.");
        }
        $this->blue = $blue;
    }

    public function getAlpha(): int
    {
        return $this->alpha;
    }

    /**
     * @param float $alpha Value between 0 and 1; 0 for completely opaque, 1 for completely transparent.
     */
    public function setAlpha(float $alpha): void
    {
        if ($alpha < 0 || $alpha > 1) {
            throw new InvalidArgumentException("\$alpha be between 0 and 1, $alpha given.");
        }
        $this->alpha = (int) ($alpha * 127);
    }
}
