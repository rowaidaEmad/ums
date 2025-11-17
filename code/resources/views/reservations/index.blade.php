@extends('layouts.app')
@section('content')
<h1>Reservations</h1>
<a href="{{ route('reservations.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>room_id</th><th>user_id</th><th>starts_at</th><th>ends_at</th><th>purpose</th><th>status</th><th>Actions</th></tr>
    @foreach($reservations as $reservation)
    <tr>
        <td>{{ $reservation->room_id }}</td><td>{{ $reservation->user_id }}</td><td>{{ $reservation->starts_at }}</td><td>{{ $reservation->ends_at }}</td><td>{{ $reservation->purpose }}</td><td>{{ $reservation->status }}</td>
        <td>
            <a href="{{ route('reservations.edit', $reservation) }}">Edit</a>
            <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $reservations.links() }}
@endsection
