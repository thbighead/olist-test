<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return ProductResource::collection(
            Product::filter(request()->query())
                ->with(['category'])
                ->paginate(self::ITEMS_PER_PAGE)
                ->withQueryString()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function store(StoreProductRequest $request)
    {
        $newProduct = new Product;

        $success = $newProduct->fill($request->only($newProduct->getFillable()))
            ->save();

        return (new ProductResource($newProduct))->additional([
            'success' => $success,
        ])->response()->setStatusCode($success ? self::HTTP_STATUS_OK : self::HTTP_STATUS_FAIL);
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     * @return ProductResource
     */
    public function show(Product $product)
    {
        $product->load(['category']);

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
        $product->load(['category']);

        $oldProduct = clone $product;

        $success = $product->update($request->only($product->getFillable()));

        return (new ProductResource($product))->additional([
            'success' => $success,
            'old' => $oldProduct->toArray(),
        ])->response()->setStatusCode($success ? self::HTTP_STATUS_OK : self::HTTP_STATUS_FAIL);
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
        $product->load(['category']);

        $success = (bool)$product->delete();

        return (new ProductResource($product))
            ->additional(['success' => $success])
            ->response()
            ->setStatusCode($success ? self::HTTP_STATUS_OK : self::HTTP_STATUS_FAIL);
    }
}
