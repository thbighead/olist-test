<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Str;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class V1CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    private const BASE_ENDPOINT = '/api/v1/category';

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_index_endpoint()
    {
        $response = $this->get(self::BASE_ENDPOINT);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'products_amount',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [[
                        'url',
                        'label',
                        'active',
                    ]],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ])
            ->assertJsonPath('meta.total', Category::count());
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_index_filtered_with_like_endpoint()
    {
        $category = Category::query()->inRandomOrder()->first();
        $initial_category_name_char = substr($category->name, 0, 1);
        $searching = [
            'name' => "{$initial_category_name_char}*",
        ];

        $response = $this->call(Request::METHOD_GET, self::BASE_ENDPOINT, $searching);
        $categoriesFound = Category::filter($searching)->paginate(10);
        $data = [
            'id',
            'name',
            'products_amount',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => key_exists(0, $response->json('data')) ? [$data] : $data,
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [[
                        'url',
                        'label',
                        'active',
                    ]],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ])
            ->assertJsonPath('meta.total', $categoriesFound->total());

        foreach (data_get($response->json('data'), '*.name') as $found_category_name) {
            $this->assertTrue(Str::startsWith(strtolower($found_category_name), $initial_category_name_char));
        }
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_index_filtered_with_equals_endpoint()
    {
        $category = Category::query()->inRandomOrder()->first();
        $searching = [
            'name' => $category->name,
        ];

        $response = $this->call(Request::METHOD_GET, self::BASE_ENDPOINT, $searching);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'products_amount',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [[
                        'url',
                        'label',
                        'active',
                    ]],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ])
            ->assertJsonPath('meta.total', 1);

        foreach (data_get($response->json('data'), '*.name') as $found_category_name) {
            $this->assertTrue($found_category_name === $category->name);
        }
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_show_endpoint()
    {
        $category = Category::query()->inRandomOrder()->first();
        $response = $this->get(self::BASE_ENDPOINT . "/{$category->id}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'products_amount',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]
            ])
            ->assertJsonPath('data.id', $category->id);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_show_endpoint_when_not_found()
    {
        $unexisting_category_id = Category::max('id') + 1000; // probably a non-existing id
        $response = $this->get(self::BASE_ENDPOINT . "/{$unexisting_category_id}");

        $response->assertNotFound();
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_store_endpoint()
    {
        $categoryUniqueName = Str::uuid()->toString(); // guaranteed to be unique
        $response = $this->call(Request::METHOD_POST, self::BASE_ENDPOINT, [
            'name' => $categoryUniqueName,
        ]);

        $response->assertCreated()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', $categoryUniqueName);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_store_endpoint_when_invalid()
    {
        $alreadyExistingCategoryName = Category::query()->inRandomOrder()->first()->name;
        $response = $this->call(Request::METHOD_POST, self::BASE_ENDPOINT, [
            'name' => $alreadyExistingCategoryName,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertHeader('Content-Type', 'application/json');
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_update_endpoint_without_modifying_resource()
    {
        $category = Category::query()->inRandomOrder()->first();
        $response = $this->call(Request::METHOD_PUT, self::BASE_ENDPOINT . "/{$category->id}", [
            'name' => $category->name,
        ]);

        $response->assertNoContent(Response::HTTP_NOT_MODIFIED);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_update_endpoint_when_not_found()
    {
        $unexisting_category_id = Category::max('id') + 1000; // probably a non-existing id
        $response = $this->call(Request::METHOD_PUT, self::BASE_ENDPOINT . "/{$unexisting_category_id}", [
            'name' => Str::random(5),
        ]);

        $response->assertNotFound();
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_update_endpoint_with_modifying_resource()
    {
        $category = Category::query()->inRandomOrder()->first();
        $categoryUniqueName = Str::uuid()->toString(); // guaranteed to be unique
        $response = $this->call(Request::METHOD_PUT, self::BASE_ENDPOINT . "/{$category->id}", [
            'name' => $categoryUniqueName,
        ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'old' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', $categoryUniqueName)
            ->assertJsonPath('old.name', $category->name);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_update_endpoint_when_invalid()
    {
        $categories = Category::query()->inRandomOrder()->get();
        $category = $categories->first();
        $categoryNameAlreadyUsed = $categories->last()->name;
        $response = $this->call(Request::METHOD_PUT, self::BASE_ENDPOINT . "/{$category->id}", [
            'name' => $categoryNameAlreadyUsed,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertHeader('Content-Type', 'application/json');
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     * @throws \Exception
     */
    public function test_restore_endpoint_for_category_without_products()
    {
        $category = Category::query()
            ->select(['categories.*'])
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->whereNull('products.category_id')
            ->inRandomOrder()
            ->first();
        $category->delete();
        $products_count_before = Product::count();
        $response = $this->call(Request::METHOD_PATCH, self::BASE_ENDPOINT . "/{$category->id}/restore");
        $products_count_after = Product::count();

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])->assertJsonPath('success', true);

        $this->assertNull($response->json('data.deleted_at'));
        $this->assertEquals($products_count_before, $products_count_after);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     * @throws \Exception
     */
    public function test_restore_endpoint_for_category_with_products()
    {
        $category = Category::query()
            ->select(['categories.*'])
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->inRandomOrder()
            ->first();
        $category->delete();
        $products_count_before = Product::count();
        $response = $this->call(Request::METHOD_PATCH, self::BASE_ENDPOINT . "/{$category->id}/restore");
        $products_count_after = Product::count();

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])->assertJsonPath('success', true);

        $this->assertNull($response->json('data.deleted_at'));
        $this->assertLessThan($products_count_after, $products_count_before);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_restore_endpoint_when_not_found()
    {
        $unexisting_category_id = Category::max('id') + 1000; // probably a non-existing id
        $response = $this->call(
            Request::METHOD_PATCH,
            self::BASE_ENDPOINT . "/{$unexisting_category_id}/restore"
        );

        $response->assertNotFound();
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_destroy_endpoint_for_category_without_products()
    {
        $category = Category::query()
            ->select(['categories.*'])
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->whereNull('products.category_id')
            ->inRandomOrder()
            ->first();
        $products_count_before = Product::count();
        $response = $this->call(Request::METHOD_DELETE, self::BASE_ENDPOINT . "/{$category->id}");
        $products_count_after = Product::count();

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])->assertJsonPath('success', true);

        $this->assertNotNull($response->json('data.deleted_at'));
        $this->assertEquals($products_count_before, $products_count_after);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_destroy_endpoint_for_category_with_products()
    {
        $category = Category::query()
            ->select(['categories.*'])
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->inRandomOrder()
            ->first();
        $products_count_before = Product::count();
        $response = $this->call(Request::METHOD_DELETE, self::BASE_ENDPOINT . "/{$category->id}");
        $products_count_after = Product::count();

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])->assertJsonPath('success', true);

        $this->assertNotNull($response->json('data.deleted_at'));
        $this->assertLessThan($products_count_before, $products_count_after);
        $this->assertEquals($products_count_before, Product::withTrashed()->count());
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_destroy_endpoint_when_not_found()
    {
        $unexisting_category_id = Category::max('id') + 1000; // probably a non-existing id
        $response = $this->call(Request::METHOD_DELETE, self::BASE_ENDPOINT . "/{$unexisting_category_id}");

        $response->assertNotFound();
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_force_destroy_endpoint_for_category_without_products()
    {
        $category = Category::query()
            ->select(['categories.*'])
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->whereNull('products.category_id')
            ->inRandomOrder()
            ->first();
        $products_count_before = Product::count();
        $response = $this->call(Request::METHOD_DELETE, self::BASE_ENDPOINT . "/{$category->id}/force");
        $products_count_after = Product::count();

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])->assertJsonPath('success', true);

        $this->assertNotNull($response->json('data.deleted_at'));
        $this->assertEquals($products_count_before, $products_count_after);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_force_destroy_endpoint_for_category_with_products()
    {
        $category = Category::query()
            ->select(['categories.*'])
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->inRandomOrder()
            ->first();
        $products_count_before = Product::count();
        $response = $this->call(Request::METHOD_DELETE, self::BASE_ENDPOINT . "/{$category->id}/force");
        $products_count_after = Product::count();

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])->assertJsonPath('success', true);

        $this->assertNull(Category::whereId($category->id)->first());
        $this->assertLessThan($products_count_before, $products_count_after);
        $this->assertEquals($products_count_after, Product::withTrashed()->count());
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_force_destroy_endpoint_when_not_found()
    {
        $unexisting_category_id = Category::max('id') + 1000; // probably a non-existing id
        $response = $this->call(
            Request::METHOD_DELETE,
            self::BASE_ENDPOINT . "/{$unexisting_category_id}/force"
        );

        $response->assertNotFound();
    }
}
