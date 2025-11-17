@extends('layouts.app')
@section('content')
<h1>Edit Course</h1>
<form method="POST" action="{{ route('courses.update', $course) }}">
@csrf @method('PUT')

<label>code</label>
<input type="text" name="code" value="{{ old('code', $course->code ?? '') }}">
@error('code')<div style="color:red">{{ $message }}</div>@enderror


<label>name</label>
<input type="text" name="name" value="{{ old('name', $course->name ?? '') }}">
@error('name')<div style="color:red">{{ $message }}</div>@enderror


<label>credits</label>
<input type="number" name="credits" value="{{ old('credits', $course->credits ?? '') }}">
@error('credits')<div style="color:red">{{ $message }}</div>@enderror


<label>is_core</label>
<input type="text" name="is_core" value="{{ old('is_core', $course->is_core ?? '') }}">
@error('is_core')<div style="color:red">{{ $message }}</div>@enderror

<button>Update</button>
</form>
@endsection
