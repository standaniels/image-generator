<?php

namespace StanDaniels\ImageGenerator\Tests;

use Mockery as M;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $targetFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->targetFile = __DIR__ . '/testfiles/target.png';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->targetFile)) {
            unlink($this->targetFile);
        }

        M::close();
    }
}
