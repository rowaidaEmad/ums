<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::latest()->paginate(10);
        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required|in:classroom,lab',
            'location' => 'nullable|string',
            'capacity' => 'nullable|integer'
        ]);

        Room::create($data);
        return redirect()->route('rooms.index')->with('success', 'Room created.');
    }

    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required|in:classroom,lab',
            'location' => 'nullable|string',
            'capacity' => 'nullable|integer'
        ]);

        $room->update($data);
        return redirect()->route('rooms.index')->with('success', 'Room updated.');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return back()->with('success', 'Room deleted.');
    }
}
