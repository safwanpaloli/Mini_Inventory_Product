@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <h2>Edit Product</h2>
        <div class="alert alert-info">
            Editing variants dynamically is a complex feature. This form allows you to update the base product information.
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label>Base Price</label>
                        <input type="number" step="0.01" min="0" name="base_price" class="form-control" value="{{ old('base_price', $product->base_price) }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Brand</label>
                        <select name="brand_id" class="form-control">
                            <option value="">No Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Hidden empty variants array to pass validation -->
                    <input type="hidden" name="variants[]" value="dummy">
                    
                    <button type="submit" class="btn btn-primary">Update Base Product</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
