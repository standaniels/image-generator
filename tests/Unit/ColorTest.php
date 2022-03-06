<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use InvalidArgumentException;
use StanDaniels\ImageGenerator\Color;
use Tests\TestCase;

class ColorTest extends TestCase
{
    public function test_it_can_be_created_from_rgb_values(): void
    {
        $color = Color::fromRgb(255, 0, 221, 50);

        self::assertSame('#ff00dd', $color->hexadecimal());
        self::assertSame(.5, $color->opacity());
    }

    /**
     * @dataProvider invalidRgbValuesProvider
     */
    public function test_it_throws_exception_if_rgb_is_out_of_range(int $r, int $g, int $b): void
    {
        try {
            Color::fromRgb($r, $g, $b);
        } catch (Exception $exception) {
            self::assertInstanceOf(InvalidArgumentException::class, $exception);

            return;
        }

        self::fail('An exception should have been thrown.');
    }

    public function test_it_can_be_created_from_a_hexadecimal_value(): void
    {
        $color = Color::fromHex('#FF00DD', 50);

        self::assertSame('#ff00dd', $color->hexadecimal());
        self::assertSame(.5, $color->opacity());
    }

    public function test_it_throws_an_exception_if_hexadecimal_is_invalid(): void
    {
        try {
            Color::fromHex('invalid');
        } catch (Exception $exception) {
            self::assertInstanceOf(InvalidArgumentException::class, $exception);

            return;
        }

        self::fail('An exception should have been thrown.');
    }

    public function test_setting_opacity_returns_a_new_instance(): void
    {
        $a = Color::fromHex('#FF00DD');
        $b = $a->setOpacity(50);

        self::assertSame(1.0, $a->opacity());
        self::assertSame(.5, $b->opacity());
    }

    /**
     * @dataProvider invalidOpacityValuesProvider
     */
    public function test_it_throws_an_exception_if_opacity_is_invalid(int $opacity): void
    {
        try {
            Color::fromHex('#FF00DD', $opacity);
        } catch (Exception $exception) {
            self::assertInstanceOf(InvalidArgumentException::class, $exception);

            return;
        }

        self::fail('An exception should have been thrown.');
    }

    public function invalidRgbValuesProvider(): array
    {
        return [
            [-1, 0, 0],
            [0, -1, 0],
            [0, 0, -1],
            [256, 0, 0],
            [0, 256, 0],
            [0, 0, 256],
        ];
    }

    public function invalidOpacityValuesProvider(): array
    {
        return [
            [-1],
            [101],
        ];
    }
}
