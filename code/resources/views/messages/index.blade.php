@extends('layouts.app')
@section('content')
<h1>Messages</h1>
<a href="{{ route('messages.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>recipient_id</th><th>subject</th><th>body</th><th>Actions</th></tr>
    @foreach($messages as $message)
    <tr>
        <td>{{ $message->recipient_id }}</td><td>{{ $message->subject }}</td><td>{{ $message->body }}</td>
        <td>
            <a href="{{ route('messages.edit', $message) }}">Edit</a>
            <form action="{{ route('messages.destroy', $message) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $messages.links() }}
@endsection
