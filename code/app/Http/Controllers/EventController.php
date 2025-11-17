<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::latest()->paginate(10);
        return view('events.index', compact('events'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'location' => 'nullable|string'
        ]);
        Event::create($data);
        return redirect()->route('events.index')->with('success','Event created.');
    }

    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'location' => 'nullable|string'
        ]);
        $event->update($data);
        return redirect()->route('events.index')->with('success','Event updated.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return back()->with('success','Event deleted.');
    }
}
