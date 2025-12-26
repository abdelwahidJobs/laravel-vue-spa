<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController
{
    public function index(Request $request)
    {
        return ProductResource::collection(Product::paginate(6));
    }
    public function show(Request $request,  Product $product)
    {
        return new ProductResource($product);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'price' => 'required',
        ]);

        return new ProductResource(Product::create($validated));
    }



    public function update(Request $request,Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required',
        ]);

        $product->update($validated);
        return new ProductResource($product);

    }


    public function delete(Request $request,Product $product)
    {
        $temp= $product;
        $product->delete();

        return new ProductResource($temp);
    }
}
