@extends('layouts.app')
@section('content')
<h1>Edit Event</h1>
<form method="POST" action="{{ route('events.update', $event) }}">
@csrf @method('PUT')

<label>title</label>
<input type="text" name="title" value="{{ old('title', $event->title ?? '') }}">
@error('title')<div style="color:red">{{ $message }}</div>@enderror


<label>description</label>
<input type="text" name="description" value="{{ old('description', $event->description ?? '') }}">
@error('description')<div style="color:red">{{ $message }}</div>@enderror


<label>starts_at</label>
<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $event->starts_at ?? '') }}">
@error('starts_at')<div style="color:red">{{ $message }}</div>@enderror


<label>ends_at</label>
<input type="datetime-local" name="ends_at" value="{{ old('ends_at', $event->ends_at ?? '') }}">
@error('ends_at')<div style="color:red">{{ $message }}</div>@enderror


<label>location</label>
<input type="text" name="location" value="{{ old('location', $event->location ?? '') }}">
@error('location')<div style="color:red">{{ $message }}</div>@enderror

<button>Update</button>
</form>
@endsection
