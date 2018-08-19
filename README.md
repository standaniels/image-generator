# Random Image Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/standaniels/image-generator.svg?style=flat-square)](https://packagist.org/packages/standaniels/image-generator)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/com/standaniels/image-generator/master.svg?style=flat-square)](https://travis-ci.com/standaniels/image-generator)
[![Quality Score](https://img.shields.io/scrutinizer/g/standaniels/image-generator.svg?style=flat-square)](https://scrutinizer-ci.com/g/standaniels/image-generator)
[![StyleCI](https://styleci.io/repos/80513668/shield?branch=master)](https://styleci.io/repos/80513668)
[![Total Downloads](https://img.shields.io/packagist/dt/standaniels/image-generator.svg?style=flat-square)](https://packagist.org/packages/standaniels/image-generator)

This package makes generating images easy. Use them for placeholders without being dependent on some external service.

```php
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Image;
use StanDaniels\ImageGenerator\Shape\Shape;

$transparency = random_int(60, 80) / 100;
$canvas = Canvas::create(400, 400, 2)
    ->background(Color::random($transparency));

for ($i = random_int(100, 150); $i > 0; $i--) {
    $transparency = random_int(60, 80) / 100;
    Shape::random($canvas, Color::random($transparency))->draw();
}

// By default, the image is stored in the directory used for temporary files
$image = Image::create($canvas);
```

Of which this could be the output:

![A randomly generated image](https://www.standaniels.nl/github/docs/image-generator/output.png)

## Installation

You can install the package via composer:

``` bash
composer require standaniels/image-generator
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
