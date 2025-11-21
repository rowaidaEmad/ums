<?php

use PHPUnit\Framework\TestCase;

class dbTest extends TestCase
{
    private string $configFile = __DIR__ . '/../ums/config.php';
    private string $dbFile = __DIR__ . '/../ums/db.php';

    protected function setUp(): void
    {
        if (!file_exists($this->configFile) || !file_exists($this->dbFile)) {
            $this->markTestSkipped("config.php or db.php not found in ums folder");
        }

        require_once $this->configFile;
        require_once $this->dbFile;
    }

    private function getSafeDB(): ?PDO
    {
        try {
            return getDB();
        } catch (PDOException $e) {
            $this->markTestSkipped("Database connection failed: " . $e->getMessage());
            return null;
        }
    }

    public function testGetDBReturnsPDOInstance(): void
    {
        $pdo = $this->getSafeDB();
        if ($pdo) {
            $this->assertInstanceOf(PDO::class, $pdo, "getDB() did not return a PDO instance");
        }
    }

    public function testGetDBSingleton(): void
    {
        $pdo = $this->getSafeDB();
        if ($pdo) {
            $pdo1 = getDB();
            $pdo2 = getDB();
            $this->assertSame($pdo1, $pdo2, "getDB() should always return the same PDO instance");
        }
    }

    public function testDatabaseConnectionAttributes(): void
    {
        $pdo = $this->getSafeDB();
        if ($pdo) {
            $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
            $this->assertEquals(PDO::FETCH_ASSOC, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
        }
    }

    public function testDSNIsCorrect(): void
    {
        $pdo = $this->getSafeDB();
        if ($pdo) {
            $expectedHost = DB_HOST;
            $actualDsn = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
            $this->assertStringContainsString($expectedHost, $actualDsn);
        }
    }
}
