@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <h2>Edit Attribute</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('attributes.update', $attribute) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $attribute->name) }}" required>
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Update Attribute</button>
                    <a href="{{ route('attributes.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
