# Random Image Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/standaniels/image-generator.svg?style=flat-square)](https://packagist.org/packages/standaniels/image-generator)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/com/standaniels/image-generator/master.svg?style=flat-square)](https://travis-ci.com/standaniels/image-generator)
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

![A randomly generated image](https://user-images.githubusercontent.com/1199737/181206232-2606ba13-0236-4a1a-af6f-a0366a29e7c0.jpg)

## Using color palettes

If you would like to generate an image based on a given set of colors like the one below, you can do it like this.

![Color palette](https://user-images.githubusercontent.com/1199737/181207233-b85a5e3a-0ea2-42a5-8263-2b4749eb5f0c.png)

```php
use StanDaniels\ImageGenerator\Canvas;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\Image;
use StanDaniels\ImageGenerator\Shape\Shape;

$colors = [
    new Color(73, 78, 109),
    new Color(214, 119, 98),
    new Color(144, 180, 148),
    new Color(237, 203, 150),
    new Color(136, 80, 83),
];

$canvas = Canvas::create(400, 400, 2)
    ->background(new Color(34, 36, 50));

for ($i = random_int(50, 100); $i > 0; $i--) {
    $color = clone $colors[random_int(0, count($colors) - 1)];
    $color->setAlpha(random_int(50, 60) / 100);
    Shape::random($canvas, $color)->draw();
}

$image = Image::create($canvas);
```

The output would be something like this:

![A randomly generated image based on a given set of colors](https://user-images.githubusercontent.com/1199737/181207373-3179c998-d682-4094-abf6-1681455cafb0.jpg)

## Installation

The library is distributed as a C extension. You compile it once against your local PHP installation, then load it like any other `.so` extension.

### Requirements

| Requirement | Notes |
|---|---|
| PHP ≥ 8.0 | Tested through PHP 8.5 |
| php-dev / php-devel | Provides `phpize` and the build headers |
| libgd | PHP must be built with GD support (`ext-gd`) |
| libexif | PHP must be built with EXIF support (`ext-exif`) |
| A C compiler | gcc or clang |
| make | Standard build tool |

On Debian / Ubuntu:

```bash
sudo apt-get install php-dev php-gd php-exif build-essential
```

On Red Hat / Fedora / CentOS:

```bash
sudo dnf install php-devel php-gd php-exif gcc make
```

On macOS (with [Homebrew](https://brew.sh)):

```bash
brew install php
# php-config, phpize, and gd are included in the Homebrew php formula
```

### Build and install

The C source lives in the `ext/` directory.

```bash
cd ext/

# 1. Prepare the build environment
phpize

# 2. Configure (links against your active PHP installation automatically)
./configure

# 3. Compile
make

# 4. Install the .so into your PHP extension directory
sudo make install
```

`make install` copies `image_generator.so` to the directory shown by `php-config --extension-dir`.

### Enable the extension

Add the extension to your `php.ini`:

```ini
extension=image_generator
```

Find the right `php.ini` with:

```bash
php --ini
```

To enable it only for a single script without editing `php.ini`:

```bash
php -d extension=image_generator script.php
```

### Verify the installation

```bash
php -r "var_dump(extension_loaded('image_generator'));"
# → bool(true)
```

### Run the test suite

The PHPT tests in `ext/tests/` are run with PHP's built-in test runner. From inside the `ext/` directory (after `make`):

```bash
make test
```

Or run them directly against the freshly built `.so`:

```bash
php run-tests.php -d extension=modules/image_generator.so tests/
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
