@extends('layouts.app')
@section('content')
<h1>Announcements</h1>
<a href="{{ route('announcements.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>title</th><th>body</th><th>published_at</th><th>Actions</th></tr>
    @foreach($announcements as $announcement)
    <tr>
        <td>{{ $announcement->title }}</td><td>{{ $announcement->body }}</td><td>{{ $announcement->published_at }}</td>
        <td>
            <a href="{{ route('announcements.edit', $announcement) }}">Edit</a>
            <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $announcements.links() }}
@endsection
