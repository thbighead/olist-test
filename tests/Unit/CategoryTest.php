<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Model;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_filtering()
    {
        $categoriesFiltered = Category::filter([
            'name' => '*a*',
        ])->get();
        $categoriesQueried = Category::where('name', 'like', '%a%')->get();

        $key = 0;
        while (($categoryFiltered = $categoriesFiltered->get($key))
            && ($categoryQueried = $categoriesQueried->get($key))) {
            /** @var Model|null $categoryFiltered */
            /** @var Model|null $categoryQueried */
            $this->assertTrue($categoryFiltered->is($categoryQueried));
            $key++;
        }

        $this->assertNull($categoriesFiltered->get($key));
        $this->assertNull($categoriesQueried->get($key));
    }

    /**
     * A basic unit test example.
     *
     * @return void
     * @throws \Exception
     */
    public function test_observers()
    {
        $category = Category::query()
            ->select(['categories.*'])
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->whereNull('products.category_id')
            ->inRandomOrder()
            ->first();

        $category->delete();
        foreach ($category->products()->withTrashed()->get() as $product) {
            /** @var Product $product */
            $this->assertNotNull($product->deleted_at);
        }

        $category->restore();
        foreach ($category->products()->get() as $product) {
            /** @var Product $product */
            $this->assertNull($product->deleted_at);
        }

        $category->forceDelete();
        $this->assertEmpty($category->products()->withTrashed()->get());
    }
}
