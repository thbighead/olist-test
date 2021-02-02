<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    const MIN_PRODUCTS_PER_CATEGORY = 0;
    const MAX_PRODUCTS_PER_CATEGORY = 50;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Category::all() as $category) {
            if ($category->id === 1) {
                continue; // forcing skipping first category to have an example without products
            }

            $how_many = rand(self::MIN_PRODUCTS_PER_CATEGORY, self::MAX_PRODUCTS_PER_CATEGORY);

            if ($how_many === 0) {
                continue;
            }

            Product::factory()
                ->count($how_many)
                ->for($category)
                ->create();
        }
    }
}
