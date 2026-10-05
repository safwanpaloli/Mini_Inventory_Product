@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Attributes</h2>
            <a href="{{ route('attributes.create') }}" class="btn btn-primary">Add Attribute</a>
        </div>
        <table class="table table-bordered bg-white shadow-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Values Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attributes as $attribute)
                <tr>
                    <td>{{ $attribute->id }}</td>
                    <td>{{ $attribute->name }}</td>
                    <td>{{ $attribute->values->count() }}</td>
                    <td>
                        <a href="{{ route('attributes.show', $attribute) }}" class="btn btn-sm btn-secondary">Manage Values</a>
                        <a href="{{ route('attributes.edit', $attribute) }}" class="btn btn-sm btn-info">Edit</a>
                        <form action="{{ route('attributes.destroy', $attribute) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No attributes found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
