<?php

use PHPUnit\Framework\TestCase;

// 1) Stub require_role() BEFORE includes
if (!function_exists('require_role')) {
    function require_role($role) {
        $GLOBALS['__require_role_called'] = $role;
        return true;
    }
}

class StudentRegisterTest extends TestCase
{
    protected function setUp(): void
    {
        // Use Alice Student (id=2)
        $_SESSION = [
            'user' => ['id' => 2]
        ];
        unset($GLOBALS['__require_role_called']);

        // Create a fake auth.php so require_once loads it without errors
        $fakeAuthDir = __DIR__ . '/ums';
        if (!is_dir($fakeAuthDir)) mkdir($fakeAuthDir, 0777, true);
        file_put_contents($fakeAuthDir . '/auth.php', "<?php\n// fake auth.php for tests\n");

        // Add test/ums to include_path so require_once 'auth.php' loads OUR fake one
        set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/ums');
    }

    public function testStudentRegisterPageShowsEnrolledCourses()
    {
        // Capture output
        ob_start();
        include __DIR__ . '/../ums/student_register.php';
        $output = ob_get_clean();

        // Assert require_role stub was triggered
        $this->assertEquals('student', $GLOBALS['__require_role_called']);

        // Assert page shows Alice's enrollment in CS102
        $this->assertStringContainsString('Course Registration', $output);
        $this->assertStringContainsString('CS102', $output);
        $this->assertStringContainsString('Data Structures', $output);
        $this->assertStringContainsString('Enrolled', $output);
    }
}
