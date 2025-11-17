@extends('layouts.app')
@section('content')
<h1>Payroll Entries</h1>
<a href="{{ route('payroll_entries.create') }}">Create</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>user_id</th><th>amount</th><th>period_start</th><th>period_end</th><th>status</th><th>Actions</th></tr>
    @foreach($payroll_entries as $payroll_entrie)
    <tr>
        <td>{{ $payroll_entrie->user_id }}</td><td>{{ $payroll_entrie->amount }}</td><td>{{ $payroll_entrie->period_start }}</td><td>{{ $payroll_entrie->period_end }}</td><td>{{ $payroll_entrie->status }}</td>
        <td>
            <a href="{{ route('payroll_entries.edit', $payroll_entrie) }}">Edit</a>
            <form action="{{ route('payroll_entries.destroy', $payroll_entrie) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $payroll_entries.links() }}
@endsection
