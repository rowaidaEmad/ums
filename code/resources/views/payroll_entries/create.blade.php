@extends('layouts.app')
@section('content')
<h1>Create Payroll Entrie</h1>
<form method="POST" action="{{ route('payroll_entries.store') }}">
@csrf

<label>user_id</label>
<input type="number" name="user_id" value="{{ old('user_id', $payroll_entrie->user_id ?? '') }}">
@error('user_id')<div style="color:red">{{ $message }}</div>@enderror


<label>amount</label>
<input type="number" name="amount" value="{{ old('amount', $payroll_entrie->amount ?? '') }}">
@error('amount')<div style="color:red">{{ $message }}</div>@enderror


<label>period_start</label>
<input type="date" name="period_start" value="{{ old('period_start', $payroll_entrie->period_start ?? '') }}">
@error('period_start')<div style="color:red">{{ $message }}</div>@enderror


<label>period_end</label>
<input type="date" name="period_end" value="{{ old('period_end', $payroll_entrie->period_end ?? '') }}">
@error('period_end')<div style="color:red">{{ $message }}</div>@enderror


<label>status</label>
<input type="text" name="status" value="{{ old('status', $payroll_entrie->status ?? '') }}">
@error('status')<div style="color:red">{{ $message }}</div>@enderror

<button>Save</button>
</form>
@endsection
