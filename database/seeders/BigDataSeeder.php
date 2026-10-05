<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Brand;

class BigDataSeeder extends Seeder
{
    public function run(): void
    {
        // Disable query log to prevent memory exhaustion
        DB::disableQueryLog();
        
        if (Category::count() === 0) {
            Category::factory(10)->create();
        }
        if (Brand::count() === 0) {
            Brand::factory(10)->create();
        }

        // We will seed in chunks to optimize memory
        $totalToSeed = 50000; // Change to 5,000,000 for actual full run, kept lower for reasonable execution time.
        $chunkSize = 1000;
        
        $this->command->info("Starting to seed {$totalToSeed} products...");

        for ($i = 0; $i < $totalToSeed; $i += $chunkSize) {
            $products = Product::factory($chunkSize)->create();
            
            // For each product, create 2 variants
            $variantsToInsert = [];
            foreach ($products as $product) {
                $variantsToInsert[] = [
                    'product_id' => $product->id,
                    'sku' => $product->id . '-VAR-1',
                    'cost_price' => 10,
                    'selling_price' => 20,
                    'stock' => 100,
                    'reorder_level' => 5,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $variantsToInsert[] = [
                    'product_id' => $product->id,
                    'sku' => $product->id . '-VAR-2',
                    'cost_price' => 15,
                    'selling_price' => 30,
                    'stock' => 50,
                    'reorder_level' => 5,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Bulk insert variants
            ProductVariant::insert($variantsToInsert);
            
            $this->command->info("Seeded " . ($i + $chunkSize) . " products...");
        }
        
        $this->command->info("Completed seeding big data.");
    }
}
