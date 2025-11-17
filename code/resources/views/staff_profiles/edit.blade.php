@extends('layouts.app')
@section('content')
<h1>Edit Staff Profile</h1>
<form method="POST" action="{{ route('staff_profiles.update', $staff_profile) }}">
@csrf @method('PUT')

<label>user_id</label>
<input type="number" name="user_id" value="{{ old('user_id', $staff_profile->user_id ?? '') }}">
@error('user_id')<div style="color:red">{{ $message }}</div>@enderror


<label>title</label>
<input type="text" name="title" value="{{ old('title', $staff_profile->title ?? '') }}">
@error('title')<div style="color:red">{{ $message }}</div>@enderror


<label>office_hours</label>
<input type="text" name="office_hours" value="{{ old('office_hours', $staff_profile->office_hours ?? '') }}">
@error('office_hours')<div style="color:red">{{ $message }}</div>@enderror


<label>department</label>
<input type="text" name="department" value="{{ old('department', $staff_profile->department ?? '') }}">
@error('department')<div style="color:red">{{ $message }}</div>@enderror


<label>bio</label>
<input type="text" name="bio" value="{{ old('bio', $staff_profile->bio ?? '') }}">
@error('bio')<div style="color:red">{{ $message }}</div>@enderror

<button>Update</button>
</form>
@endsection
