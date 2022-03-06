<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use InvalidArgumentException;
use StanDaniels\ImageGenerator\Color;
use StanDaniels\ImageGenerator\ImageFactory;
use Tests\TestCase;

class ImageFactoryTest extends TestCase
{
    /**
     * @dataProvider invalidParametersProvider
     */
    public function test_it_throws_an_exception_if_parameter_is_invalid($width, $height, $colors): void
    {
        try {
            new ImageFactory($width, $height, $colors);
        } catch (Exception $e) {
            self::assertInstanceOf(InvalidArgumentException::class, $e);

            return;
        }

        self::fail('An exception should have been thrown');
    }

    public function test_the_new_static_method_is_an_alias_for_the_constructor(): void
    {
        $a = new ImageFactory(1, 2, [Color::fromRgb(0, 0, 0)]);
        $b = ImageFactory::new(1, 2, [Color::fromRgb(0, 0, 0)]);

        self::assertEquals($a, $b);
    }

    public function test_it_can_create_an_image(): void
    {
        $image = ImageFactory::new(
            100,
            200,
            [Color::fromHex('#00ff00')],
            Color::fromHex('#ff00ff')
        )->create();

        $background = $image->svg()->getDocument()->getElementsByTagName('rect')[0] ?? null;
        $polygons = $image->svg()->getDocument()->getElementsByTagName('polygon');

        self::assertSame('100', $image->svg()->getDocument()->getWidth());
        self::assertSame('200', $image->svg()->getDocument()->getHeight());

        self::assertNotNull($background, 'Failed to assert that the image has a background.');
        self::assertSame('#ff00ff', $background->getAttribute('fill'), 'Failed to assert that the background is #ff00ff.');

        self::assertGreaterThanOrEqual(20, count($polygons));
        foreach ($polygons as $polygon) {
            self::assertSame('#00ff00', $polygon->getAttribute('fill'));
        }
    }

    public function invalidParametersProvider(): array
    {
        return [
            'invalid_width' => [0, 1, [Color::fromRgb(0, 0, 0)]],
            'invalid_height' => [1, 0, [Color::fromRgb(0, 0, 0)]],
            'invalid_color' => [1, 1, ['not_a_color']],
            'no_colors' => [1, 1, []],
        ];
    }
}
