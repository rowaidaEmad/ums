@extends('layouts.app')
@section('content')
<h1>Performance Records</h1>
<a href="{{ route('performance_records.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>user_id</th><th>period</th><th>score</th><th>notes</th><th>Actions</th></tr>
    @foreach($performance_records as $performance_record)
    <tr>
        <td>{{ $performance_record->user_id }}</td><td>{{ $performance_record->period }}</td><td>{{ $performance_record->score }}</td><td>{{ $performance_record->notes }}</td>
        <td>
            <a href="{{ route('performance_records.edit', $performance_record) }}">Edit</a>
            <form action="{{ route('performance_records.destroy', $performance_record) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $performance_records.links() }}
@endsection
