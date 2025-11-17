@extends('layouts.app')
@section('content')
<h1>Edit Equipment</h1>
<form method="POST" action="{{ route('equipment.update', $item) }}">
@csrf @method('PUT')

<label>name</label>
<input type="text" name="name" value="{{ old('name', $item->name ?? '') }}">
@error('name')<div style="color:red">{{ $message }}</div>@enderror


<label>serial</label>
<input type="text" name="serial" value="{{ old('serial', $item->serial ?? '') }}">
@error('serial')<div style="color:red">{{ $message }}</div>@enderror


<label>status</label>
<input type="text" name="status" value="{{ old('status', $item->status ?? '') }}">
@error('status')<div style="color:red">{{ $message }}</div>@enderror


<label>room_id</label>
<input type="number" name="room_id" value="{{ old('room_id', $item->room_id ?? '') }}">
@error('room_id')<div style="color:red">{{ $message }}</div>@enderror


<label>staff_id</label>
<input type="number" name="staff_id" value="{{ old('staff_id', $item->staff_id ?? '') }}">
@error('staff_id')<div style="color:red">{{ $message }}</div>@enderror


<label>warranty_years</label>
<input type="number" name="warranty_years" value="{{ old('warranty_years', $item->warranty_years ?? '') }}">
@error('warranty_years')<div style="color:red">{{ $message }}</div>@enderror

<button>Update</button>
</form>
@endsection
