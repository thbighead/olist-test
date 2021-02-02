<?php

namespace Tests\Feature;

use App\Models\Category;
use Str;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class V1CategoryTest extends TestCase
{
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
        $searching = [
            'name' => 'i*',
        ];

        $response = $this->call(Request::METHOD_GET, self::BASE_ENDPOINT, $searching);
        $categoriesFound = Category::filter($searching)->paginate(10);

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
            ->assertJsonPath('meta.total', $categoriesFound->total());

        foreach (data_get($response->json('data'), '*.name') as $found_category_name) {
            $this->assertTrue(Str::startsWith(strtolower($found_category_name), 'i'));
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
                'data' => [[
                    'id',
                    'name',
                    'products_amount',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]]
            ]);

        foreach (data_get($response->json('data'), '*.id') as $found_category_id) {
            $this->assertTrue($found_category_id === $category->id);
        }
    }
}
