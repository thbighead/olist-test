<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return CategoryResource
     */
    public function index()
    {
        return new CategoryResource(Category::paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCategoryRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function store(StoreCategoryRequest $request)
    {
        $newCategory = new Category;

        $success = $newCategory->fill($request->only($newCategory->getFillable()))
            ->save();

        return (new CategoryResource($newCategory))->additional([
            'success' => $success,
        ])->response()->setStatusCode($success ? 200 : 500);
    }

    /**
     * Display the specified resource.
     *
     * @param Category $category
     * @return CategoryResource
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCategoryRequest $request
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $oldCategory = clone $category;

        $success = $category->update($request->only($category->getFillable()));

        return (new CategoryResource($category))->additional([
            'success' => $success,
            'old' => $oldCategory->toArray(),
        ])->response()->setStatusCode($success ? 200 : 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Exception
     */
    public function destroy(Category $category)
    {
        $success = (bool)$category->delete();

        return (new CategoryResource($category))
            ->additional(['success' => $success])
            ->response()
            ->setStatusCode($success ? 200 : 500);
    }
}
