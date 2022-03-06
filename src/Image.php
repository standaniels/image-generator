<?php

declare(strict_types=1);

namespace StanDaniels\ImageGenerator;

use Imagick;
use SVG\SVG;

final class Image
{
    public function __construct(private SVG $svg, private Imagick $imagick)
    {
    }

    public function svg(): SVG
    {
        return $this->svg;
    }

    public function savePng(string $path): bool
    {
        $this->imagick->readImageBlob($this->svg->toXMLString());
        $this->imagick->setImageFormat('png');

        return $this->imagick->writeImage($path);
    }
}
