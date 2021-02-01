<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sku' => $this->faker->unique()->bothify('???##########'),
            'name' => ucfirst($this->faker->word),
            'description' => $this->faker->realText(),
            'price' => $this->faker->randomFloat(2, 5, 999),
        ];
    }
}
