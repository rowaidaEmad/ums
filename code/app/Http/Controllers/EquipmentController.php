<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipment = Equipment::latest()->with(['room','staff'])->paginate(10);
        return view('equipment.index', compact('equipment'));
    }

    public function create()
    {
        $rooms = Room::all();
        $staff = User::whereIn('role', ['professor','ta','staff','admin'])->get();
        return view('equipment.create', compact('rooms','staff'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'serial' => 'nullable|string|unique:equipment,serial',
            'status' => 'required|in:available,allocated,maintenance',
            'room_id' => 'nullable|exists:rooms,id',
            'staff_id' => 'nullable|exists:users,id'
        ]);

        $e = Equipment::create($data);

        // EAV example (optional fields)
        if ($request->filled('warranty_years')) {
            $e->setAttributeValue('warranty_years', (int)$request->input('warranty_years'));
        }

        return redirect()->route('equipment.index')->with('success', 'Equipment created.');
    }

    public function edit(Equipment $equipment)
    {
        $rooms = Room::all();
        $staff = User::whereIn('role', ['professor','ta','staff','admin'])->get();
        return view('equipment.edit', compact('equipment','rooms','staff'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $data = $request->validate([
            'name' => 'required',
            'serial' => 'nullable|string|unique:equipment,serial,' . $equipment->id,
            'status' => 'required|in:available,allocated,maintenance',
            'room_id' => 'nullable|exists:rooms,id',
            'staff_id' => 'nullable|exists:users,id'
        ]);

        $equipment->update($data);

        if ($request->filled('warranty_years')) {
            $equipment->setAttributeValue('warranty_years', (int)$request->input('warranty_years'));
        }

        return redirect()->route('equipment.index')->with('success', 'Equipment updated.');
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();
        return back()->with('success', 'Equipment deleted.');
    }
}
