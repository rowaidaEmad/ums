@extends('layouts.app')
@section('content')
<h1>Create Grade</h1>
<form method="POST" action="{{ route('grades.store') }}">
@csrf

<label>assessment_id</label>
<input type="number" name="assessment_id" value="{{ old('assessment_id', $grade->assessment_id ?? '') }}">
@error('assessment_id')<div style="color:red">{{ $message }}</div>@enderror


<label>student_id</label>
<input type="number" name="student_id" value="{{ old('student_id', $grade->student_id ?? '') }}">
@error('student_id')<div style="color:red">{{ $message }}</div>@enderror


<label>score</label>
<input type="number" name="score" value="{{ old('score', $grade->score ?? '') }}">
@error('score')<div style="color:red">{{ $message }}</div>@enderror

<button>Save</button>
</form>
@endsection
