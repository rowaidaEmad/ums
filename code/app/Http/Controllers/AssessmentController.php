<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function index()
    {
        $assessments = Assessment::with('course')->latest()->paginate(10);
        return view('assessments.index', compact('assessments'));
    }

    public function create()
    {
        $courses = Course::all();
        return view('assessments.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required',
            'max_score' => 'required|integer|min:1',
            'due_date' => 'required|date'
        ]);

        Assessment::create($data);
        return redirect()->route('assessments.index')->with('success', 'Assessment created.');
    }

    public function edit(Assessment $assessment)
    {
        $courses = Course::all();
        return view('assessments.edit', compact('assessment','courses'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required',
            'max_score' => 'required|integer|min:1',
            'due_date' => 'required|date'
        ]);

        $assessment->update($data);
        return redirect()->route('assessments.index')->with('success', 'Assessment updated.');
    }

    public function destroy(Assessment $assessment)
    {
        $assessment->delete();
        return back()->with('success', 'Assessment deleted.');
    }
}
