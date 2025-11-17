<?php

namespace App\Http\Controllers;

use App\Models\PerformanceRecord;
use App\Models\User;
use Illuminate\Http\Request;

class PerformanceRecordController extends Controller
{
    public function index()
    {
        $performance_records = PerformanceRecord::with('user')->latest()->paginate(10);
        return view('performance_records.index', compact('performance_records'));
    }

    public function create()
    {
        $users = User::whereIn('role',['professor','ta','staff','admin'])->get();
        return view('performance_records.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'period' => 'required|string',
            'score' => 'required|integer',
            'notes' => 'nullable|string'
        ]);
        PerformanceRecord::create($data);
        return redirect()->route('performance-records.index')->with('success','Record created.');
    }

    public function edit(PerformanceRecord $performance_record)
    {
        $users = User::whereIn('role',['professor','ta','staff','admin'])->get();
        return view('performance_records.edit', ['performance_record'=>$performance_record,'users'=>$users]);
    }

    public function update(Request $request, PerformanceRecord $performance_record)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'period' => 'required|string',
            'score' => 'required|integer',
            'notes' => 'nullable|string'
        ]);
        $performance_record->update($data);
        return redirect()->route('performance-records.index')->with('success','Record updated.');
    }

    public function destroy(PerformanceRecord $performance_record)
    {
        $performance_record->delete();
        return back()->with('success','Record deleted.');
    }
}
