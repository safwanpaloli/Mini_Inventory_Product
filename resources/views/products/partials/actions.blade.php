<div class="btn-group" role="group">
    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info" title="View">
        <i class="fas fa-eye"></i> View
    </a>
    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning" title="Edit">
        <i class="fas fa-edit"></i> Edit
    </a>
    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
            <i class="fas fa-trash"></i> Delete
        </button>
    </form>
</div>
