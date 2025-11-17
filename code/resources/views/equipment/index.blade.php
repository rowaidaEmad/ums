@extends('layouts.app')
@section('content')
<h1>Equipment</h1>
<a href="{{ route('equipment.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>name</th><th>serial</th><th>status</th><th>room_id</th><th>staff_id</th><th>warranty_years</th><th>Actions</th></tr>
    @foreach($equipment as $item)
    <tr>
        <td>{{ $item->name }}</td><td>{{ $item->serial }}</td><td>{{ $item->status }}</td><td>{{ $item->room_id }}</td><td>{{ $item->staff_id }}</td><td>{{ $item->warranty_years }}</td>
        <td>
            <a href="{{ route('equipment.edit', $item) }}">Edit</a>
            <form action="{{ route('equipment.destroy', $item) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $equipment.links() }}
@endsection
