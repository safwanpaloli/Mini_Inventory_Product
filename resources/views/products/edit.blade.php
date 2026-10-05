@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <h2>Edit Product</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
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
                    
                    <div class="mb-3">
                        <label>Thumbnail</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                        @if($product->thumbnail)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="Current Thumbnail" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                <small class="text-muted d-block mt-1">Current Image</small>
                            </div>
                        @endif
                    </div>
                    
                    <hr>
                    <h4 class="mb-3">Manage Variants</h4>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Variant</th>
                                    <th>SKU</th>
                                    <th>Cost Price</th>
                                    <th>Selling Price</th>
                                    <th>Stock</th>
                                    <th>Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->variants as $variant)
                                <tr>
                                    <td>
                                        <strong>{{ $variant->attributeValues->pluck('value')->join(' / ') ?: 'Default' }}</strong>
                                        <input type="hidden" name="variants[{{ $variant->id }}][id]" value="{{ $variant->id }}">
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm" name="variants[{{ $variant->id }}][sku]" value="{{ $variant->sku }}" required></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="variants[{{ $variant->id }}][cost_price]" value="{{ $variant->cost_price }}"></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="variants[{{ $variant->id }}][selling_price]" value="{{ $variant->selling_price }}" required></td>
                                    <td><input type="number" min="0" class="form-control form-control-sm" name="variants[{{ $variant->id }}][stock]" value="{{ $variant->stock }}" required></td>
                                    <td>
                                        <select class="form-select form-select-sm" name="variants[{{ $variant->id }}][active]">
                                            <option value="1" {{ $variant->active ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ !$variant->active ? 'selected' : '' }}>No</option>
                                        </select>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No variants found for this product.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Update Product & Variants</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
