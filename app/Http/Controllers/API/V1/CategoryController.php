<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return CategoryResource::collection(
            Category::filter(request()->query())
                ->withCount(['products'])
                ->paginate(self::ITEMS_PER_PAGE)
                ->withQueryString()
        );
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
        ])->response()->setStatusCode($success ? Response::HTTP_CREATED : Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Display the specified resource.
     *
     * @param Category $category
     * @return CategoryResource
     */
    public function show(Category $category)
    {
        $category->loadCount(['products']);

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
        $nothing_to_change = true;
        $fieldsToChange = $request->only($category->getFillable());
        foreach ($fieldsToChange as $attribute => $supposed_new_value) {
            if ($category->getActualAttribute($attribute) !== $supposed_new_value) {
                $nothing_to_change = false;
                break;
            }
        }
        if ($nothing_to_change) {
            return response()->json(null, Response::HTTP_NOT_MODIFIED);
        }

        $category->loadCount(['products']);

        $oldCategory = clone $category;

        $success = $category->update($fieldsToChange);

        return (new CategoryResource($category))->additional([
            'success' => $success,
            'old' => $oldCategory->toArray(),
        ])->response()->setStatusCode($success ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Softly remove the specified resource from storage.
     *
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Exception
     */
    public function destroy(Category $category)
    {
        $category->loadCount(['products']);

        $success = (bool)$category->delete();

        return (new CategoryResource($category))
            ->additional(['success' => $success])
            ->response()
            ->setStatusCode($success ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function restore($id)
    {
        $destroyedCategory = Category::withTrashed()->findOrFail($id)->loadCount(['products']);
        $success = (bool)$destroyedCategory->restore();

        return (new CategoryResource($destroyedCategory))
            ->additional(['success' => $success])
            ->response()
            ->setStatusCode($success ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Completely remove the specified resource from storage.
     *
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function forceDestroy(Category $category)
    {
        $category->loadCount(['products']);

        $success = (bool)$category->forceDelete();

        return (new CategoryResource($category))
            ->additional(['success' => $success])
            ->response()
            ->setStatusCode($success ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
