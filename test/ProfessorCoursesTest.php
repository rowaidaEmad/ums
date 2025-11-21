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

class ProfessorCoursesTest extends TestCase
{
    protected function setUp(): void
    {
        // Set session to Dr. Smith (id=4)
        $_SESSION = [
            'user' => ['id' => 4]
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

    public function testProfessorCoursesDisplaysAssignedCourses()
    {
        ob_start();
        include __DIR__ . '/../ums/professor_courses.php';
        $output = ob_get_clean();

        $this->assertEquals('professor', $GLOBALS['__require_role_called']);
        $this->assertStringContainsString('My Assigned Courses', $output);
        $this->assertStringContainsString('CS102', $output);
        $this->assertStringContainsString('Data Structures', $output);
        $this->assertStringContainsString('Enter Grades', $output);
        $this->assertStringContainsString('Room 101', $output);
        $this->assertStringContainsString('Yes', $output); // is_core
    }

    public function testProfessorCoursesShowsNoCoursesIfNoneAssigned()
    {
        $_SESSION['user']['id'] = 5; // professor with no courses

        ob_start();
        include __DIR__ . '/../ums/professor_courses.php';
        $output = ob_get_clean();

        $this->assertEquals('professor', $GLOBALS['__require_role_called']);
        $this->assertStringContainsString('No courses assigned yet.', $output);
    }
}
