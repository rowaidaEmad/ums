@extends('layouts.app')
@section('content')
<h1>Grades</h1>
<a href="{{ route('grades.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>assessment_id</th><th>student_id</th><th>score</th><th>Actions</th></tr>
    @foreach($grades as $grade)
    <tr>
        <td>{{ $grade->assessment_id }}</td><td>{{ $grade->student_id }}</td><td>{{ $grade->score }}</td>
        <td>
            <a href="{{ route('grades.edit', $grade) }}">Edit</a>
            <form action="{{ route('grades.destroy', $grade) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $grades.links() }}
@endsection
