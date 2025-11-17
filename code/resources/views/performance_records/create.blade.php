@extends('layouts.app')
@section('content')
<h1>Create Performance Record</h1>
<form method="POST" action="{{ route('performance_records.store') }}">
@csrf

<label>user_id</label>
<input type="number" name="user_id" value="{{ old('user_id', $performance_record->user_id ?? '') }}">
@error('user_id')<div style="color:red">{{ $message }}</div>@enderror


<label>period</label>
<input type="text" name="period" value="{{ old('period', $performance_record->period ?? '') }}">
@error('period')<div style="color:red">{{ $message }}</div>@enderror


<label>score</label>
<input type="number" name="score" value="{{ old('score', $performance_record->score ?? '') }}">
@error('score')<div style="color:red">{{ $message }}</div>@enderror


<label>notes</label>
<input type="text" name="notes" value="{{ old('notes', $performance_record->notes ?? '') }}">
@error('notes')<div style="color:red">{{ $message }}</div>@enderror

<button>Save</button>
</form>
@endsection
