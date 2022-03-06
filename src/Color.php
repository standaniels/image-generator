<?php

declare(strict_types=1);

namespace StanDaniels\ImageGenerator;

use InvalidArgumentException;

final class Color
{
    private float $opacity = 1;

    private function __construct(private int $red, private int $green, private int $blue)
    {
    }

    /**
     * @param  int  $red  0-255
     * @param  int  $green  0-255
     * @param  int  $blue  0-255
     * @param  int  $opacity  0-100
     * @return \StanDaniels\ImageGenerator\Color
     */
    public static function fromRgb(int $red, int $green, int $blue, int $opacity = 100): self
    {
        $validate = static function ($value, $name) {
            if ($value < 0 || $value > 255) {
                throw new InvalidArgumentException(
                    sprintf('%s value must be between 0 and 255, [%s] given', $name, $value),
                );
            }
        };

        $validate($red, 'red');
        $validate($green, 'green');
        $validate($blue, 'blue');

        return (new self($red, $green, $blue))->setOpacity($opacity);
    }

    /**
     * Create a new color from a hexadecimal string.
     *
     * @param  string  $hexadecimal
     * @param  int  $opacity  0-100
     * @return \StanDaniels\ImageGenerator\Color
     */
    public static function fromHex(string $hexadecimal, int $opacity = 100): self
    {
        if (! preg_match('/^#?([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})$/i', $hexadecimal, $matches)) {
            throw new InvalidArgumentException(
                sprintf('Hexadecimal must be in the format #RRGGBB, [%s] given.', $hexadecimal),
            );
        }

        $red = (int) hexdec($matches[1]);
        $green = (int) hexdec($matches[2]);
        $blue = (int) hexdec($matches[3]);

        return (new self($red, $green, $blue))->setOpacity($opacity);
    }

    /**
     * @param  int  $opacity  0-100
     * @return \StanDaniels\ImageGenerator\Color
     */
    public function setOpacity(int $opacity): self
    {
        if ($opacity < 0 || $opacity > 100) {
            throw new InvalidArgumentException(
                sprintf('Opacity value must be between 0 and 100, [%s] given.', $opacity),
            );
        }

        $clone = clone $this;
        $clone->opacity = $opacity / 100;

        return $clone;
    }

    public function opacity(): float
    {
        return $this->opacity;
    }

    /**
     * @return string Color in hexadecimal format.
     */
    public function hexadecimal(): string
    {
        return sprintf('#%02x%02x%02x', $this->red, $this->green, $this->blue);
    }
}
