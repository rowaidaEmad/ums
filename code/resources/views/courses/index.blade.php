@extends('layouts.app')
@section('content')
<h1>Courses</h1>
<a href="{{ route('courses.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>code</th><th>name</th><th>credits</th><th>is_core</th><th>Actions</th></tr>
    @foreach($courses as $course)
    <tr>
        <td>{{ $course->code }}</td><td>{{ $course->name }}</td><td>{{ $course->credits }}</td><td>{{ $course->is_core }}</td>
        <td>
            <a href="{{ route('courses.edit', $course) }}">Edit</a>
            <form action="{{ route('courses.destroy', $course) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $courses.links() }}
@endsection
