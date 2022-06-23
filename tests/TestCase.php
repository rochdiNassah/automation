<?php declare(strict_types=1);

namespace Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        \Automation\Core\Application::instance()->run();
    }
}