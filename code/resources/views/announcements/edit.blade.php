@extends('layouts.app')
@section('content')
<h1>Edit Announcement</h1>
<form method="POST" action="{{ route('announcements.update', $announcement) }}">
@csrf @method('PUT')

<label>title</label>
<input type="text" name="title" value="{{ old('title', $announcement->title ?? '') }}">
@error('title')<div style="color:red">{{ $message }}</div>@enderror


<label>body</label>
<input type="text" name="body" value="{{ old('body', $announcement->body ?? '') }}">
@error('body')<div style="color:red">{{ $message }}</div>@enderror


<label>published_at</label>
<input type="datetime-local" name="published_at" value="{{ old('published_at', $announcement->published_at ?? '') }}">
@error('published_at')<div style="color:red">{{ $message }}</div>@enderror

<button>Update</button>
</form>
@endsection
