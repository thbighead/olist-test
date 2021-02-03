<?php

namespace Tests\Unit;

use App\Models\Model;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
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
        $productsFiltered = Product::filter([
            'name' => '*a*',
        ])->get();
        $productsQueried = Product::where('name', 'like', '%a%')->get();

        $key = 0;
        while (($productFiltered = $productsFiltered->get($key))
            && ($productQueried = $productsQueried->get($key))) {
            /** @var Model|null $productFiltered */
            /** @var Model|null $productQueried */
            $this->assertTrue($productFiltered->is($productQueried));
            $key++;
        }

        $this->assertNull($productsFiltered->get($key));
        $this->assertNull($productsQueried->get($key));

        $productsFiltered = Product::filter([
            'sku' => '*0*',
        ])->get();
        $productsQueried = Product::where('sku', 'like', '%0%')->get();

        $key = 0;
        while (($productFiltered = $productsFiltered->get($key))
            && ($productQueried = $productsQueried->get($key))) {
            /** @var Model|null $productFiltered */
            /** @var Model|null $productQueried */
            $this->assertTrue($productFiltered->is($productQueried));
            $key++;
        }

        $this->assertNull($productsFiltered->get($key));
        $this->assertNull($productsQueried->get($key));

        $productsFiltered = Product::filter([
            'name' => '*a*',
            'sku' => '*0*',
        ])->get();
        $productsQueried = Product::where('name', 'like', '%a%')
            ->where('sku', 'like', '%0%')->get();

        $key = 0;
        while (($productFiltered = $productsFiltered->get($key))
            && ($productQueried = $productsQueried->get($key))) {
            /** @var Model|null $productFiltered */
            /** @var Model|null $productQueried */
            $this->assertTrue($productFiltered->is($productQueried));
            $key++;
        }

        $this->assertNull($productsFiltered->get($key));
        $this->assertNull($productsQueried->get($key));
    }

    public function test_attributes_accessors()
    {
        $product = Product::query()->inRandomOrder()->first();

        $this->assertNotEquals($product->getActualAttribute('price'), $product->price);
        $this->assertMatchesRegularExpression('/^R\$ \d+,\d{2}$/', $product->price);
        $this->assertIsNumeric($product->getActualAttribute('price'));
        $this->assertMatchesRegularExpression('/^\d+.\d{2}$/', $product->getActualAttribute('price'));
    }
}
