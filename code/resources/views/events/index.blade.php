@extends('layouts.app')
@section('content')
<h1>Events</h1>
<a href="{{ route('events.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>title</th><th>description</th><th>starts_at</th><th>ends_at</th><th>location</th><th>Actions</th></tr>
    @foreach($events as $event)
    <tr>
        <td>{{ $event->title }}</td><td>{{ $event->description }}</td><td>{{ $event->starts_at }}</td><td>{{ $event->ends_at }}</td><td>{{ $event->location }}</td>
        <td>
            <a href="{{ route('events.edit', $event) }}">Edit</a>
            <form action="{{ route('events.destroy', $event) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $events.links() }}
@endsection
