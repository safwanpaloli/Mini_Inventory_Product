@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Manage Stock</h2>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Current Stock</th>
                                <th>Reorder Level</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($variants as $variant)
                            @php 
                                $isLowStock = $variant->stock <= $variant->reorder_level;
                            @endphp
                            <tr class="{{ $isLowStock ? 'table-warning' : '' }}">
                                <td>{{ $variant->product->name }}</td>
                                <td>{{ $variant->sku }}</td>
                                <td><strong>{{ $variant->stock }}</strong></td>
                                <td>{{ $variant->reorder_level }}</td>
                                <td>
                                    @if($isLowStock)
                                        <span class="badge bg-danger">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">OK</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary adjust-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#adjustStockModal"
                                            data-id="{{ $variant->id }}"
                                            data-name="{{ $variant->product->name }} - {{ $variant->sku }}"
                                            data-stock="{{ $variant->stock }}">
                                        Adjust Stock
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 d-flex justify-content-center">
                    {{ $variants->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('stock.adjust') }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title">Adjust Stock</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="variant_id" id="modalVariantId">
            
            <div class="mb-3">
                <label>Variant</label>
                <input type="text" id="modalVariantName" class="form-control" readonly>
            </div>
            
            <div class="mb-3">
                <label>Current Stock</label>
                <input type="text" id="modalCurrentStock" class="form-control" readonly>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Action</label>
                    <select name="type" class="form-control" required>
                        <option value="in">Add Stock (In)</option>
                        <option value="out">Reduce Stock (Out)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Quantity</label>
                    <input type="number" name="qty" min="1" class="form-control" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label>Reason (Mandatory)</label>
                <input type="text" name="reason" class="form-control" placeholder="e.g. New delivery, damaged goods..." required>
            </div>
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Adjustment</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const adjustBtns = document.querySelectorAll('.adjust-btn');
    adjustBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modalVariantId').value = this.getAttribute('data-id');
            document.getElementById('modalVariantName').value = this.getAttribute('data-name');
            document.getElementById('modalCurrentStock').value = this.getAttribute('data-stock');
        });
    });
});
</script>
@endsection
