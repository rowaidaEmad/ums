@extends('layouts.app')
@section('content')
<h1>Staff Profiles</h1>
<a href="{{ route('staff_profiles.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>user_id</th><th>title</th><th>office_hours</th><th>department</th><th>bio</th><th>Actions</th></tr>
    @foreach($staff_profiles as $staff_profile)
    <tr>
        <td>{{ $staff_profile->user_id }}</td><td>{{ $staff_profile->title }}</td><td>{{ $staff_profile->office_hours }}</td><td>{{ $staff_profile->department }}</td><td>{{ $staff_profile->bio }}</td>
        <td>
            <a href="{{ route('staff_profiles.edit', $staff_profile) }}">Edit</a>
            <form action="{{ route('staff_profiles.destroy', $staff_profile) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $staff_profiles.links() }}
@endsection
