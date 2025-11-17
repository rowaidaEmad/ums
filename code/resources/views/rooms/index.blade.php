@extends('layouts.app')
@section('content')
<h1>Rooms</h1>
<a href="{{ route('rooms.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>name</th><th>type</th><th>location</th><th>capacity</th><th>Actions</th></tr>
    @foreach($rooms as $room)
    <tr>
        <td>{{ $room->name }}</td><td>{{ $room->type }}</td><td>{{ $room->location }}</td><td>{{ $room->capacity }}</td>
        <td>
            <a href="{{ route('rooms.edit', $room) }}">Edit</a>
            <form action="{{ route('rooms.destroy', $room) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $rooms.links() }}
@endsection
