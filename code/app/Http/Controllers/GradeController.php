<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index()
    {
        $grades = Grade::with(['assessment','student'])->latest()->paginate(10);
        return view('grades.index', compact('grades'));
    }

    public function create()
    {
        $assessments = Assessment::all();
        $students = User::where('role', 'student')->get();
        return view('grades.create', compact('assessments','students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'student_id' => 'required|exists:users,id',
            'score' => 'required|integer|min:0'
        ]);

        Grade::create($data);
        return redirect()->route('grades.index')->with('success', 'Grade saved.');
    }

    public function edit(Grade $grade)
    {
        $assessments = Assessment::all();
        $students = User::where('role', 'student')->get();
        return view('grades.edit', compact('grade','assessments','students'));
    }

    public function update(Request $request, Grade $grade)
    {
        $data = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'student_id' => 'required|exists:users,id',
            'score' => 'required|integer|min:0'
        ]);

        $grade->update($data);
        return redirect()->route('grades.index')->with('success', 'Grade updated.');
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
        return back()->with('success', 'Grade deleted.');
    }
}
