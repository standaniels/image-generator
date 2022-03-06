<?php

declare(strict_types=1);

namespace StanDaniels\ImageGenerator;

use Imagick;
use InvalidArgumentException;
use SVG\Nodes\Shapes\SVGRect;
use SVG\SVG;

final class ImageFactory
{
    private float $geometricMean;

    /**
     * @param  int  $width
     * @param  int  $height
     * @param  Color[]  $shapeColors
     * @param ?Color  $background
     * @throws InvalidArgumentException
     */
    public function __construct(
        private int $width,
        private int $height,
        private array $shapeColors,
        private ?Color $background = null
    ) {
        if (empty($this->shapeColors)) {
            throw new InvalidArgumentException('At least one shape color is required');
        }

        foreach ($shapeColors as $color) {
            if (! $color instanceof Color) {
                throw new InvalidArgumentException('Shape colors must be an array of Color objects');
            }
        }

        if ($width < 1) {
            throw new InvalidArgumentException('Width must be greater than 0');
        }

        if ($height < 1) {
            throw new InvalidArgumentException('Height must be greater than 0');
        }

        $this->geometricMean = floor(sqrt($this->width * $this->height));
    }

    /**
     * @param  int  $width
     * @param  int  $height
     * @param  Color[]  $shapeColors
     * @param ?Color  $background
     * @return self
     * @throws InvalidArgumentException
     */
    public static function new(int $width, int $height, array $shapeColors, ?Color $background = null): self
    {
        return new self($width, $height, $shapeColors, $background);
    }

    public function create(): Image
    {
        $svg = new SVG($this->width, $this->height);

        if ($this->background) {
            $background = new SVGRect(0, 0, $this->width, $this->height);
            $background->setAttribute('fill', $this->background->hexadecimal());
            $background->setAttribute('fill-opacity', $this->background->opacity());
            $svg->getDocument()->addChild($background);
        }

        for ($i = random_int(20, 30); $i > 0; $i--) {
            $svg->getDocument()->addChild(Polygon::new(
                x: random_int(0, $this->width),
                y: random_int(0, $this->height),
                size: random_int((int) ($this->geometricMean / 8), (int) ($this->geometricMean / 4)),
                sides: random_int(5, 10),
                rotate: random_int(0, 360),
                color: $this->shapeColors[random_int(0, count($this->shapeColors) - 1)]->setOpacity(random_int(50, 60)),
            ));
        }

        return new Image($svg, new Imagick());
    }
}
