<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->paginate(10);
        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'body' => 'required',
            'published_at' => 'nullable|date'
        ]);
        Announcement::create($data);
        return redirect()->route('announcements.index')->with('success','Announcement posted.');
    }

    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title' => 'required',
            'body' => 'required',
            'published_at' => 'nullable|date'
        ]);
        $announcement->update($data);
        return redirect()->route('announcements.index')->with('success','Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success','Announcement deleted.');
    }
}
