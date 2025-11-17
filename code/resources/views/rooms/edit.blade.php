@extends('layouts.app')
@section('content')
<h1>Edit Room</h1>
<form method="POST" action="{{ route('rooms.update', $room) }}">
@csrf @method('PUT')

<label>name</label>
<input type="text" name="name" value="{{ old('name', $room->name ?? '') }}">
@error('name')<div style="color:red">{{ $message }}</div>@enderror


<label>type</label>
<input type="text" name="type" value="{{ old('type', $room->type ?? '') }}">
@error('type')<div style="color:red">{{ $message }}</div>@enderror


<label>location</label>
<input type="text" name="location" value="{{ old('location', $room->location ?? '') }}">
@error('location')<div style="color:red">{{ $message }}</div>@enderror


<label>capacity</label>
<input type="number" name="capacity" value="{{ old('capacity', $room->capacity ?? '') }}">
@error('capacity')<div style="color:red">{{ $message }}</div>@enderror

<button>Update</button>
</form>
@endsection
