<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with('room','user')->latest()->paginate(10);
        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        $rooms = Room::all();
        return view('reservations.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'purpose' => 'nullable|string'
        ]);

        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        Reservation::create($data);
        return redirect()->route('reservations.index')->with('success', 'Reservation requested.');
    }

    public function edit(Reservation $reservation)
    {
        $rooms = Room::all();
        return view('reservations.edit', compact('reservation','rooms'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'purpose' => 'nullable|string',
            'status' => 'required|in:pending,approved,declined'
        ]);

        $reservation->update($data);
        return redirect()->route('reservations.index')->with('success', 'Reservation updated.');
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return back()->with('success', 'Reservation deleted.');
    }
}
