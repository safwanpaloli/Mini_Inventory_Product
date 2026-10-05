@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Stock Movements Ledger</h2>
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-body bg-light">
                <form id="filter-form" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="in">In (Addition)</option>
                            <option value="out">Out (Reduction)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control">
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
                    <table class="table table-bordered table-hover" id="movements-table">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Date & Time</th>
                                <th>Product / SKU</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Reason</th>
                                <th>User</th>
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
        var table = $('#movements-table').DataTable({
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            ajax: {
                url: "{{ route('stock-movements.index') }}",
                data: function (d) {
                    d.type = $('#type').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'id', name: 'stock_movements.id' },
                { data: 'created_at', name: 'stock_movements.created_at' },
                { data: 'product_name', name: 'product_name', orderable: false },
                { data: 'type', name: 'stock_movements.type' },
                { data: 'qty', name: 'stock_movements.qty' },
                { data: 'reason', name: 'stock_movements.reason' },
                { data: 'user_name', name: 'user_name', orderable: false }
            ]
        });

        $('#filter-btn').click(function() {
            table.draw();
        });
    });
</script>
@endpush
