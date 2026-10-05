@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title">Welcome to Dashboard, {{ auth()->user()->name }}!</h4>
                <p class="card-text">Your current role is: <strong>{{ auth()->user()->role }}</strong>.</p>
                <hr>
                <p>Use the navigation menu to manage the system.</p>
            </div>
        </div>
    </div>
</div>
@endsection
