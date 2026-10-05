@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6">
        <h2>{{ $attribute->name }} Values</h2>
        
        <table class="table table-bordered bg-white shadow-sm mt-3">
            <thead>
                <tr>
                    <th>Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attribute->values as $value)
                <tr>
                    <td>{{ $value->value }}</td>
                    <td>
                        <form action="{{ route('attribute-values.destroy', $value) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center">No values found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm mt-5">
            <div class="card-header bg-light">Add New Value</div>
            <div class="card-body">
                <form action="{{ route('attribute-values.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="attribute_id" value="{{ $attribute->id }}">
                    <div class="mb-3">
                        <label>Value (e.g. Small, Red)</label>
                        <input type="text" name="value" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Value</button>
                    <a href="{{ route('attributes.index') }}" class="btn btn-secondary">Back to Attributes</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
