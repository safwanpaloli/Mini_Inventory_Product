@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Products</h2>
            @if(in_array(auth()->user()->role, ['admin', 'manager']))
                <a href="{{ route('products.create') }}" class="btn btn-primary">Add Product</a>
            @endif
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-body bg-light">
                <form id="filter-form" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Brand</label>
                        <select name="brand_id" id="brand_id" class="form-select">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="filter-btn" class="btn btn-secondary w-100">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="products-table">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Thumbnail</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Base Price</th>
                                <th>Variants</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#products-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('products.index') }}",
                data: function (d) {
                    d.category_id = $('#category_id').val();
                    d.brand_id = $('#brand_id').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                { data: 'id', name: 'products.id' },
                { 
                    data: 'thumbnail', 
                    name: 'thumbnail',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        if(data) {
                            return '<img src="/storage/' + data + '" style="width: 50px; height: 50px; object-fit: cover;" class="img-thumbnail">';
                        }
                        return '<div class="bg-light text-center text-muted d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 10px; border: 1px solid #ddd;">No Img</div>';
                    }
                },
                { data: 'name', name: 'products.name' },
                { data: 'category_name', name: 'category_name' },
                { data: 'brand_name', name: 'brand_name' },
                { 
                    data: 'base_price', 
                    name: 'products.base_price',
                    render: function(data) {
                        return '$' + parseFloat(data).toFixed(2);
                    }
                },
                { data: 'variants_count', name: 'variants_count', searchable: false },
                { 
                    data: 'status', 
                    name: 'products.status',
                    render: function(data) {
                        return data === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#filter-btn').click(function() {
            table.draw();
        });
    });
</script>
@endpush
