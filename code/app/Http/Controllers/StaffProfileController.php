<?php

namespace App\Http\Controllers;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;

class StaffProfileController extends Controller
{
    public function index()
    {
        $staff_profiles = StaffProfile::with('user')->latest()->paginate(10);
        return view('staff_profiles.index', compact('staff_profiles'));
    }

    public function create()
    {
        $users = User::whereIn('role',['professor','ta','staff','admin'])->get();
        return view('staff_profiles.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'nullable|string',
            'office_hours' => 'nullable|string',
            'department' => 'nullable|string',
            'bio' => 'nullable|string'
        ]);
        StaffProfile::create($data);
        return redirect()->route('staff-profiles.index')->with('success','Profile created.');
    }

    public function edit(StaffProfile $staff_profile)
    {
        $users = User::whereIn('role',['professor','ta','staff','admin'])->get();
        return view('staff_profiles.edit', ['staff_profile'=>$staff_profile,'users'=>$users]);
    }

    public function update(Request $request, StaffProfile $staff_profile)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'nullable|string',
            'office_hours' => 'nullable|string',
            'department' => 'nullable|string',
            'bio' => 'nullable|string'
        ]);
        $staff_profile->update($data);
        return redirect()->route('staff-profiles.index')->with('success','Profile updated.');
    }

    public function destroy(StaffProfile $staff_profile)
    {
        $staff_profile->delete();
        return back()->with('success','Profile deleted.');
    }
}
