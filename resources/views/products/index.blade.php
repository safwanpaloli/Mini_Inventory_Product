@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Products</h2>
            <a href="{{ route('products.create') }}" class="btn btn-primary">Add Product</a>
        </div>
        <table class="table table-bordered bg-white shadow-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? 'None' }}</td>
                    <td>${{ number_format($product->base_price, 2) }}</td>
                    <td>{{ $product->variants->sum('stock') }}</td>
                    <td>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-info">Edit</a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
