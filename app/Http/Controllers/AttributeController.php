<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attribute;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->get();
        return view('attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('attributes.create');
    }

    public function store(\App\Http\Requests\StoreAttributeRequest $request)
    {
        Attribute::create($request->validated());
        return redirect()->route('attributes.index')->with('success', 'Attribute added successfully!');
    }

    public function edit(Attribute $attribute)
    {
        return view('attributes.edit', compact('attribute'));
    }

    public function update(\App\Http\Requests\UpdateAttributeRequest $request, Attribute $attribute)
    {
        $attribute->update($request->validated());
        return redirect()->route('attributes.index')->with('success', 'Attribute updated successfully!');
    }

    public function show(Attribute $attribute)
    {
        return view('attributes.show', compact('attribute'));
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return back()->with('success', 'Attribute deleted successfully!');
    }
}
