<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ProductResource
     */
    public function index()
    {
        return new ProductResource(Product::paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     * @return ProductResource
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $oldProduct = clone $product;

        $success = $product->update($request->only($product->getFillable()));

        return (new ProductResource($product))->additional([
            'success' => $success,
            'old' => $oldProduct->toArray(),
        ])->response()->setStatusCode($success ? 200 : 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Exception
     */
    public function destroy(Product $product)
    {
        $success = (bool)$product->delete();

        return (new ProductResource($product))
            ->additional(['success' => $success])
            ->response()
            ->setStatusCode($success ? 200 : 500);
    }
}
