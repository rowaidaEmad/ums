@extends('layouts.app')
@section('content')
<h1>Assessments</h1>
<a href="{{ route('assessments.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>course_id</th><th>title</th><th>max_score</th><th>due_date</th><th>Actions</th></tr>
    @foreach($assessments as $assessment)
    <tr>
        <td>{{ $assessment->course_id }}</td><td>{{ $assessment->title }}</td><td>{{ $assessment->max_score }}</td><td>{{ $assessment->due_date }}</td>
        <td>
            <a href="{{ route('assessments.edit', $assessment) }}">Edit</a>
            <form action="{{ route('assessments.destroy', $assessment) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $assessments.links() }}
@endsection
