<?php

namespace App\Http\Controllers;

use App\Models\PayrollEntry;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollEntryController extends Controller
{
    public function index()
    {
        $payroll_entries = PayrollEntry::with('user')->latest()->paginate(10);
        return view('payroll_entries.index', compact('payroll_entries'));
    }

    public function create()
    {
        $users = User::whereIn('role',['professor','ta','staff','admin'])->get();
        return view('payroll_entries.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'status' => 'required|in:pending,paid'
        ]);
        PayrollEntry::create($data);
        return redirect()->route('payroll-entries.index')->with('success','Entry created.');
    }

    public function edit(PayrollEntry $payroll_entry)
    {
        $users = User::whereIn('role',['professor','ta','staff','admin'])->get();
        return view('payroll_entries.edit', ['payroll_entry'=>$payroll_entry,'users'=>$users]);
    }

    public function update(Request $request, PayrollEntry $payroll_entry)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'status' => 'required|in:pending,paid'
        ]);
        $payroll_entry->update($data);
        return redirect()->route('payroll-entries.index')->with('success','Entry updated.');
    }

    public function destroy(PayrollEntry $payroll_entry)
    {
        $payroll_entry->delete();
        return back()->with('success','Entry deleted.');
    }
}
