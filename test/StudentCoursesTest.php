<?php

use PHPUnit\Framework\TestCase;

/**
 * Stub require_role() BEFORE includes
 */
if (!function_exists('require_role')) {
    function require_role($role) {
        $GLOBALS['__require_role_called'] = $role;
        return true; // prevent exit()
    }
}

class StudentCoursesTest extends TestCase
{
    protected function setUp(): void
    {
        // Set session to Alice Student (id=2)
        $_SESSION = [
            'user' => ['id' => 2]
        ];
        unset($GLOBALS['__require_role_called']);

        // test/ums folder exists and stub auth.php to avoid calling real require_role()
        $fakeAuthDir = __DIR__ . '/ums';
        if (!is_dir($fakeAuthDir)) {
            mkdir($fakeAuthDir, 0777, true);
        }
        file_put_contents($fakeAuthDir . '/auth.php', "<?php\n// fake auth.php for tests\n");

        // Add test/ums to include path
        set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/ums');
    }

    public function testStudentCoursesLoadsWithRealDB()
    {
        // Capture the output of the student_courses.php page
        ob_start();
        include __DIR__ . '/../ums/student_courses.php';
        $output = ob_get_clean();

        // Assert that require_role stub was triggered
        $this->assertEquals('student', $GLOBALS['__require_role_called']);

        // Assert output contains the course and grade we inserted
        $this->assertStringContainsString('My Courses & Grades', $output);
        $this->assertStringContainsString('CS102', $output);
        $this->assertStringContainsString('Data Structures', $output);
        $this->assertStringContainsString('Dr. Smith', $output);
        $this->assertStringContainsString('A', $output);
    }
}
