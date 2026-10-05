<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::all();
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(\App\Http\Requests\StoreBrandRequest $request)
    {
        Brand::create($request->validated());
        return redirect()->route('brands.index')->with('success', 'Brand added successfully!');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(\App\Http\Requests\UpdateBrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());
        return redirect()->route('brands.index')->with('success', 'Brand updated successfully!');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return back()->with('success', 'Brand deleted successfully!');
    }
}
