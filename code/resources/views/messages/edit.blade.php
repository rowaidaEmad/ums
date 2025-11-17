@extends('layouts.app')
@section('content')
<h1>Edit Message</h1>
<form method="POST" action="{{ route('messages.update', $message) }}">
@csrf @method('PUT')

<label>recipient_id</label>
<input type="number" name="recipient_id" value="{{ old('recipient_id', $message->recipient_id ?? '') }}">
@error('recipient_id')<div style="color:red">{{ $message }}</div>@enderror


<label>subject</label>
<input type="text" name="subject" value="{{ old('subject', $message->subject ?? '') }}">
@error('subject')<div style="color:red">{{ $message }}</div>@enderror


<label>body</label>
<input type="text" name="body" value="{{ old('body', $message->body ?? '') }}">
@error('body')<div style="color:red">{{ $message }}</div>@enderror

<button>Update</button>
</form>
@endsection
