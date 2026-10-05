<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $cost = $this->faker->randomFloat(2, 5, 500);
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper($this->faker->bothify('???-###-???')),
            'cost_price' => $cost,
            'selling_price' => $cost * 1.5,
            'stock' => $this->faker->numberBetween(0, 1000),
            'reorder_level' => 5,
            'active' => true,
        ];
    }
}
