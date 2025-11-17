@extends('layouts.app')
@section('content')
<h1>Edit Assessment</h1>
<form method="POST" action="{{ route('assessments.update', $assessment) }}">
@csrf @method('PUT')

<label>course_id</label>
<input type="number" name="course_id" value="{{ old('course_id', $assessment->course_id ?? '') }}">
@error('course_id')<div style="color:red">{{ $message }}</div>@enderror


<label>title</label>
<input type="text" name="title" value="{{ old('title', $assessment->title ?? '') }}">
@error('title')<div style="color:red">{{ $message }}</div>@enderror


<label>max_score</label>
<input type="number" name="max_score" value="{{ old('max_score', $assessment->max_score ?? '') }}">
@error('max_score')<div style="color:red">{{ $message }}</div>@enderror


<label>due_date</label>
<input type="date" name="due_date" value="{{ old('due_date', $assessment->due_date ?? '') }}">
@error('due_date')<div style="color:red">{{ $message }}</div>@enderror

<button>Update</button>
</form>
@endsection
