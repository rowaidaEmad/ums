<?php

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private string $configFile = __DIR__ . '/../ums/config.php';

    protected function setUp(): void
    {
        // Ensure config.php exists
        if (!file_exists($this->configFile)) {
            $this->markTestSkipped("config.php not found in ums folder");
        }
        require_once $this->configFile;
    }

    public function testConfigConstantsExist(): void
    {
        $this->assertTrue(defined('DB_HOST'), "DB_HOST is not defined");
        $this->assertTrue(defined('DB_NAME'), "DB_NAME is not defined");
        $this->assertTrue(defined('DB_USER'), "DB_USER is not defined");
        $this->assertTrue(defined('DB_PASS'), "DB_PASS is not defined");
    }

    public function testConfigConstantValues(): void
    {
        $this->assertEquals('localhost', DB_HOST);
        $this->assertEquals('ums', DB_NAME);
        $this->assertEquals('root', DB_USER);
        $this->assertEquals('', DB_PASS); // Password is empty for XAMPP
    }
}
