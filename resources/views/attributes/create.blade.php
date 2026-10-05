@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <h2>Add Attribute</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('attributes.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Name (e.g. Size, Color)</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Attribute</button>
                    <a href="{{ route('attributes.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
