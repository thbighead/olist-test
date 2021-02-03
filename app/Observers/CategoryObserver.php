<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Product;

class CategoryObserver
{
    /**
     * Handle the Category "deleted" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function deleted(Category $category)
    {
        $category->products()->delete();
    }

    /**
     * Handle the Category "restored" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function restored(Category $category)
    {
        $category->products()->withTrashed()->get()->each(function (Product $product) {
            $product->restore();
        });
    }
}
