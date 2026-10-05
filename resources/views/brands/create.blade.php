@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <h2>Add Brand</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('brands.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Brand</button>
                    <a href="{{ route('brands.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
