<?php

use PHPUnit\Framework\TestCase;

/**
 * 1) Stub require_role() BEFORE includes
 */
if (!function_exists('require_role')) {
    function require_role($role) {
        $GLOBALS['__require_role_called'] = $role;
        return true; // prevent exit()
    }
}

class ProfessorGradesTest extends TestCase
{
    protected function setUp(): void
    {
        // Set session to Dr. Smith (professor id = 4)
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

    public function testProfessorCanViewAssignedCourse()
    {
        // Assume CS102 (id=3) is assigned to Dr. Smith
        $_GET['course_id'] = 3;

        ob_start();
        include __DIR__ . '/../ums/professor_grades.php';
        $output = ob_get_clean();

        $this->assertEquals('professor', $GLOBALS['__require_role_called']);

        // Check that assigned course appears
        $this->assertStringContainsString('Grades for CS102 - Data Structures', $output);

        // Check that enrolled student Alice is shown
        $this->assertStringContainsString('Alice Student', $output);
        $this->assertStringContainsString('alice@student.test', $output);
        $this->assertStringContainsString('A', $output); // grade we inserted
    }

    public function testProfessorCannotViewUnassignedCourse()
    {
        // Assume CS101 (id=1) is NOT assigned to Dr. Smith
        $_GET['course_id'] = 1;

        ob_start();
        include __DIR__ . '/../ums/professor_grades.php';
        $output = ob_get_clean();

        // The page should display the "not assigned" message
        $this->assertStringContainsString(
            "Course not found or you are not assigned to it.",
            $output
        );
    }
}
