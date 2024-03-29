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
            'name' => $this->faker->word(),
            'external_id' => $this->faker->uuid(),
            'is_active' => true,
            'meta' => [
                'off' => $this->faker->randomNumber(2),
                'price' => $this->faker->randomFloat(2, 1, 1000),
                'price_id' => $this->faker->uuid(),
                'features' => $this->faker->randomElements([
                    $this->faker->word(),
                    $this->faker->word(),
                    $this->faker->word()
                ])
            ]
        ];
    }
}
