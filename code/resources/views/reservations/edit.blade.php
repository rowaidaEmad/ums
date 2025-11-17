@extends('layouts.app')
@section('content')
<h1>Edit Reservation</h1>
<form method="POST" action="{{ route('reservations.update', $reservation) }}">
@csrf @method('PUT')

<label>room_id</label>
<input type="number" name="room_id" value="{{ old('room_id', $reservation->room_id ?? '') }}">
@error('room_id')<div style="color:red">{{ $message }}</div>@enderror


<label>user_id</label>
<input type="number" name="user_id" value="{{ old('user_id', $reservation->user_id ?? '') }}">
@error('user_id')<div style="color:red">{{ $message }}</div>@enderror


<label>starts_at</label>
<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $reservation->starts_at ?? '') }}">
@error('starts_at')<div style="color:red">{{ $message }}</div>@enderror


<label>ends_at</label>
<input type="datetime-local" name="ends_at" value="{{ old('ends_at', $reservation->ends_at ?? '') }}">
@error('ends_at')<div style="color:red">{{ $message }}</div>@enderror


<label>purpose</label>
<input type="text" name="purpose" value="{{ old('purpose', $reservation->purpose ?? '') }}">
@error('purpose')<div style="color:red">{{ $message }}</div>@enderror


<label>status</label>
<input type="text" name="status" value="{{ old('status', $reservation->status ?? '') }}">
@error('status')<div style="color:red">{{ $message }}</div>@enderror

<button>Update</button>
</form>
@endsection
