@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2>Create Product</h2>
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('products.store') }}" id="productForm" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required id="productName">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Base Price</label>
                    <input type="number" step="0.01" min="0" name="base_price" class="form-control" required id="basePrice">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Brand</label>
                    <select name="brand_id" class="form-control">
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Thumbnail</label>
                    <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*">
                    @error('thumbnail')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <hr>
            <h4>Attributes</h4>
            <div class="row align-items-end mb-3">
                <div class="col-md-4">
                    <label>Select Attribute</label>
                    <select id="attributeSelect" class="form-control">
                        <option value="">-- Select Attribute --</option>
                        @foreach($attributes as $attribute)
                            <option value="{{ $attribute->id }}" data-name="{{ $attribute->name }}">{{ $attribute->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label>Select Values</label>
                    <div id="valuesContainer" class="border p-2 bg-light" style="min-height: 38px; border-radius: 4px;">
                        <span class="text-muted small">Select an attribute first</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-primary" id="addAttributeBtn">Add Attribute</button>
                </div>
            </div>

            <!-- List of selected attributes for the product -->
            <div id="selectedAttributesList" class="mb-3"></div>

            <button type="button" class="btn btn-secondary mt-2 mb-4" id="generateVariantsBtn">Generate Variants</button>

            <div class="table-responsive">
                <table class="table table-bordered d-none" id="variantsTable">
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
                    <tbody id="variantsBody">
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-5">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pass attributes data to JS
    const attributesData = @json($attributes);
    
    const attributeSelect = document.getElementById('attributeSelect');
    const valuesContainer = document.getElementById('valuesContainer');
    const addAttributeBtn = document.getElementById('addAttributeBtn');
    const selectedAttributesList = document.getElementById('selectedAttributesList');
    const generateBtn = document.getElementById('generateVariantsBtn');
    const tbody = document.getElementById('variantsBody');
    const table = document.getElementById('variantsTable');

    let selectedAttributes = {}; // e.g., { "Size": [{id:1, name:'S'}, {id:2, name:'M'}] }

    // When an attribute is selected in the dropdown
    attributeSelect.addEventListener('change', function() {
        const attrId = this.value;
        valuesContainer.innerHTML = '';
        
        if(!attrId) {
            valuesContainer.innerHTML = '<span class="text-muted small">Select an attribute first</span>';
            return;
        }

        const attr = attributesData.find(a => a.id == attrId);
        if(attr && attr.values.length > 0) {
            attr.values.forEach(val => {
                const div = document.createElement('div');
                div.className = 'form-check form-check-inline';
                div.innerHTML = `
                    <input class="form-check-input temp-value-cb" type="checkbox" value="${val.id}" data-name="${val.value}">
                    <label class="form-check-label">${val.value}</label>
                `;
                valuesContainer.appendChild(div);
            });
        } else {
            valuesContainer.innerHTML = '<span class="text-muted small">No values found.</span>';
        }
    });

    // Add selected attribute and its values to our working list
    addAttributeBtn.addEventListener('click', function() {
        const attrId = attributeSelect.value;
        if(!attrId) return alert('Please select an attribute');
        
        const attrName = attributeSelect.options[attributeSelect.selectedIndex].getAttribute('data-name');
        
        let checkedValues = [];
        document.querySelectorAll('.temp-value-cb:checked').forEach(cb => {
            checkedValues.push({
                id: cb.value,
                name: cb.getAttribute('data-name')
            });
        });

        if(checkedValues.length === 0) return alert('Please select at least one value');

        // Merge if exists, otherwise create new
        if(!selectedAttributes[attrName]) {
            selectedAttributes[attrName] = [];
        }
        
        // Add only unique values
        checkedValues.forEach(cv => {
            if(!selectedAttributes[attrName].find(v => v.id === cv.id)) {
                selectedAttributes[attrName].push(cv);
            }
        });

        renderSelectedAttributes();
        
        // Reset inputs
        attributeSelect.value = '';
        valuesContainer.innerHTML = '<span class="text-muted small">Select an attribute first</span>';
    });

    function renderSelectedAttributes() {
        selectedAttributesList.innerHTML = '';
        Object.keys(selectedAttributes).forEach(attrName => {
            const vals = selectedAttributes[attrName].map(v => v.name).join(', ');
            const div = document.createElement('div');
            div.className = 'badge bg-info text-dark me-2 p-2 mb-2';
            div.innerHTML = `<strong>${attrName}:</strong> ${vals} <span style="cursor:pointer; margin-left:8px; font-weight:bold" onclick="removeAttribute('${attrName}')">x</span>`;
            selectedAttributesList.appendChild(div);
        });
    }

    window.removeAttribute = function(attrName) {
        delete selectedAttributes[attrName];
        renderSelectedAttributes();
        table.classList.add('d-none');
        tbody.innerHTML = '';
    }

    generateBtn.addEventListener('click', function() {
        const attrNames = Object.keys(selectedAttributes);
        if(attrNames.length === 0) {
            alert('Please add at least one attribute to generate variants.');
            return;
        }

        // Cartesian product generator
        const cartesian = (arrays) => {
            return arrays.reduce((a, b) => 
                a.map(x => b.map(y => x.concat([y]))).reduce((c, d) => c.concat(d), [])
            , [[]]);
        };

        const arraysToCombine = attrNames.map(k => selectedAttributes[k]);
        const combinations = cartesian(arraysToCombine);

        tbody.innerHTML = '';
        const basePriceInput = document.getElementById('basePrice');
        const nameInput = document.getElementById('productName');
        const basePrice = basePriceInput.value || 0;
        const baseName = nameInput.value ? nameInput.value.substring(0,3).toUpperCase() : 'PRD';

        combinations.forEach((combo, index) => {
            const variantName = combo.map(c => c.name).join('/');
            const valIds = combo.map(c => c.id).join(',');
            const sku = `${baseName}-${combo.map(c => c.name.substring(0,3).toUpperCase()).join('-')}-${index+1}`;
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    ${variantName}
                    <input type="hidden" name="variants[${index}][values]" value="${valIds}">
                </td>
                <td><input type="text" class="form-control form-control-sm" name="variants[${index}][sku]" value="${sku}" required></td>
                <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="variants[${index}][cost_price]" value="0"></td>
                <td><input type="number" step="0.01" min="0" class="form-control form-control-sm variant-price" name="variants[${index}][selling_price]" value="${basePrice}" required></td>
                <td><input type="number" min="0" class="form-control form-control-sm" name="variants[${index}][stock]" value="0" required></td>
                <td>
                    <select class="form-select form-select-sm" name="variants[${index}][active]">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            `;
            tbody.appendChild(tr);
        });

        table.classList.remove('d-none');
    });

    document.getElementById('productForm').addEventListener('submit', function(e) {
        let valid = true;
        document.querySelectorAll('#variantsBody tr').forEach(tr => {
            const cost = parseFloat(tr.querySelector('input[name*="[cost_price]"]').value) || 0;
            const selling = parseFloat(tr.querySelector('input[name*="[selling_price]"]').value) || 0;
            if(selling < cost) {
                valid = false;
                tr.classList.add('table-danger');
            } else {
                tr.classList.remove('table-danger');
            }
        });
        
        if(!valid) {
            e.preventDefault();
            alert('Warning: One or more variants have a selling price below the cost price.');
        }
    });
});
</script>
@endsection
