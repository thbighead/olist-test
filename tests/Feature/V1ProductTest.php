<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Str;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class V1ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    private const BASE_ENDPOINT = '/api/v1/product';

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
                    'sku',
                    'name',
                    'description',
                    'price',
                    'category' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
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
            ->assertJsonPath('meta.total', Product::count());
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_index_filtered_with_like_endpoint()
    {
        $searching = [
            'sku' => '*0*',
        ];

        $response = $this->call(Request::METHOD_GET, self::BASE_ENDPOINT, $searching);
        $ProductsFound = Product::filter($searching)->paginate(10);
        $data = [
            'id',
            'sku',
            'name',
            'description',
            'price',
            'category' => [
                'id',
                'name',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
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
            ->assertJsonPath('meta.total', $ProductsFound->total());

        foreach (data_get($response->json('data'), '*.sku') as $found_product_sku) {
            $this->assertTrue(Str::contains($found_product_sku, '0'));
        }
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_index_filtered_with_equals_endpoint()
    {
        $product = Product::query()->inRandomOrder()->first();
        $searching = [
            'sku' => $product->sku,
        ];

        $response = $this->call(Request::METHOD_GET, self::BASE_ENDPOINT, $searching);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'sku',
                    'name',
                    'description',
                    'price',
                    'category' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
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

        foreach (data_get($response->json('data'), '*.sku') as $found_product_sku) {
            $this->assertTrue($found_product_sku === $product->sku);
        }
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_show_endpoint()
    {
        $product = Product::query()->inRandomOrder()->first();
        $response = $this->get(self::BASE_ENDPOINT . "/{$product->id}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'sku',
                    'name',
                    'description',
                    'price',
                    'category' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]
            ])
            ->assertJsonPath('data.id', $product->id);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_show_endpoint_when_not_found()
    {
        $unexisting_product_id = Product::max('id') + 1000; // probably a non-existing id
        $response = $this->get(self::BASE_ENDPOINT . "/{$unexisting_product_id}");

        $response->assertNotFound();
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_store_endpoint()
    {
        $productUniqueSku = Str::uuid()->toString(); // guaranteed to be unique
        $response = $this->call(Request::METHOD_POST, self::BASE_ENDPOINT, [
            'category_id' => Category::query()->inRandomOrder()->first()->id,
            'sku' => $productUniqueSku,
            'name' => 'New name',
            'description' => 'A great and nice description',
            'price' => 50
        ]);

        $response->assertCreated()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'sku',
                    'name',
                    'description',
                    'price',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sku', $productUniqueSku);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_store_endpoint_when_invalid()
    {
        $alreadyExistingProductSku = Product::query()->inRandomOrder()->first()->sku;
        $response = $this->call(Request::METHOD_POST, self::BASE_ENDPOINT, [
            'category_id' => Category::query()->inRandomOrder()->first()->id,
            'sku' => $alreadyExistingProductSku,
            'name' => 'New name',
            'description' => 'A great and nice description',
            'price' => 50
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
        $product = Product::query()->inRandomOrder()->first();
        $response = $this->call(Request::METHOD_PUT, self::BASE_ENDPOINT . "/{$product->id}", [
            'name' => $product->name,
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
        $unexisting_product_id = Product::max('id') + 1000; // probably a non-existing id
        $response = $this->call(Request::METHOD_PUT, self::BASE_ENDPOINT . "/{$unexisting_product_id}", [
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
        $product = Product::query()->inRandomOrder()->first();
        $productUniqueSku = Str::uuid()->toString(); // guaranteed to be unique
        $response = $this->call(Request::METHOD_PUT, self::BASE_ENDPOINT . "/{$product->id}", [
            'sku' => $productUniqueSku,
        ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'sku',
                    'name',
                    'description',
                    'price',
                    'category' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'old' => [
                    'id',
                    'sku',
                    'name',
                    'description',
                    'price',
                    'category' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sku', $productUniqueSku)
            ->assertJsonPath('old.sku', $product->sku);
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_update_endpoint_when_invalid()
    {
        $products = Product::query()->inRandomOrder()->get();
        $product = $products->first();
        $productSkuAlreadyUsed = $products->last()->sku;
        $response = $this->call(Request::METHOD_PUT, self::BASE_ENDPOINT . "/{$product->id}", [
            'sku' => $productSkuAlreadyUsed,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertHeader('Content-Type', 'application/json');
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_destroy_endpoint()
    {
        $product = Product::query()->inRandomOrder()->first();
        $response = $this->call(Request::METHOD_DELETE, self::BASE_ENDPOINT . "/{$product->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'sku',
                    'name',
                    'description',
                    'price',
                    'category' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])
            ->assertJsonPath('success', true);

        $this->assertNotNull($response->json('data.deleted_at'));
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_destroy_endpoint_when_not_found()
    {
        $unexisting_product_id = Product::max('id') + 1000; // probably a non-existing id
        $response = $this->call(Request::METHOD_DELETE, self::BASE_ENDPOINT . "/{$unexisting_product_id}");

        $response->assertNotFound();
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     * @throws \Exception
     */
    public function test_restore_endpoint()
    {
        $product = Product::query()->inRandomOrder()->first();
        $product->delete();
        $response = $this->call(Request::METHOD_PATCH, self::BASE_ENDPOINT . "/{$product->id}/restore");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'sku',
                    'name',
                    'description',
                    'price',
                    'category' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])
            ->assertJsonPath('success', true);

        $this->assertNull($response->json('data.deleted_at'));
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_restore_endpoint_when_not_found()
    {
        $unexisting_product_id = Product::max('id') + 1000; // probably a non-existing id
        $response = $this->call(
            Request::METHOD_PATCH,
            self::BASE_ENDPOINT . "/{$unexisting_product_id}/restore"
        );

        $response->assertNotFound();
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_force_destroy_endpoint()
    {
        $product = Product::query()->inRandomOrder()->first();
        $response = $this->call(Request::METHOD_DELETE, self::BASE_ENDPOINT . "/{$product->id}/force");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'sku',
                    'name',
                    'description',
                    'price',
                    'category' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'success'
            ])
            ->assertJsonPath('success', true);

        $this->assertNull(Product::whereId($product->id)->first());
    }

    /**
     * A basic request to category index route.
     *
     * @return void
     */
    public function test_force_destroy_endpoint_when_not_found()
    {
        $unexisting_product_id = Product::max('id') + 1000; // probably a non-existing id
        $response = $this->call(
            Request::METHOD_DELETE,
            self::BASE_ENDPOINT . "/{$unexisting_product_id}/force"
        );

        $response->assertNotFound();
    }
}
