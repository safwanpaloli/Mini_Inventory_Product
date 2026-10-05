<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\AttributeValue;

class AttributeValueController extends Controller
{
    public function store(\App\Http\Requests\StoreAttributeValueRequest $request)
    {
        AttributeValue::create($request->validated());
        return back()->with('success', 'Value added successfully!');
    }

    public function destroy(AttributeValue $attributeValue)
    {
        $attributeValue->delete();
        return back()->with('success', 'Value deleted successfully!');
    }
}
