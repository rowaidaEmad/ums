<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::latest()->paginate(10);
        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|unique:courses,code',
            'name' => 'required',
            'credits' => 'required|integer|min:1',
            'is_core' => 'required|boolean'
        ]);

        Course::create($data);
        return redirect()->route('courses.index')->with('success', 'Course created.');
    }

    public function edit(Course $course)
    {
        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'code' => 'required|unique:courses,code,' . $course->id,
            'name' => 'required',
            'credits' => 'required|integer|min:1',
            'is_core' => 'required|boolean'
        ]);

        $course->update($data);
        return redirect()->route('courses.index')->with('success', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return back()->with('success', 'Course deleted.');
    }
}
