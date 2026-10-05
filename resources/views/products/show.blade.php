@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Product Details</h2>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to Products</a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        @if($product->thumbnail)
                            <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name }}" class="img-fluid rounded shadow-sm">
                        @else
                            <div class="bg-light text-muted d-flex align-items-center justify-content-center rounded shadow-sm" style="height: 200px;">
                                No Thumbnail
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h3>{{ $product->name }}</h3>
                        <p class="text-muted">SKU Base: {{ $product->slug }}</p>
                        
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 150px;">Base Price:</th>
                                <td>${{ number_format($product->base_price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>{{ $product->category->name ?? 'None' }}</td>
                            </tr>
                            <tr>
                                <th>Brand:</th>
                                <td>{{ $product->brand->name ?? 'None' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ $product->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <h4 class="mb-3">Variants</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>SKU</th>
                                <th>Attributes</th>
                                <th>Cost Price</th>
                                <th>Selling Price</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->variants as $variant)
                                <tr>
                                    <td>{{ $variant->sku }}</td>
                                    <td>
                                        @foreach($variant->attributeValues as $val)
                                            <span class="badge bg-info text-dark">{{ $val->value }}</span>
                                        @endforeach
                                    </td>
                                    <td>${{ number_format($variant->cost_price, 2) }}</td>
                                    <td>${{ number_format($variant->selling_price, 2) }}</td>
                                    <td>
                                        @if($variant->stock <= $variant->reorder_level)
                                            <span class="text-danger fw-bold">{{ $variant->stock }}</span>
                                        @else
                                            {{ $variant->stock }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No variants available for this product.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
