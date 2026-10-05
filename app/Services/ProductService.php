<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductService
{
    public function createProduct(array $productData, array $variantsData, ?string $thumbnailPath): Product
    {
        return DB::transaction(function () use ($productData, $variantsData, $thumbnailPath) {
            $product = Product::create([
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']) . '-' . uniqid(),
                'base_price' => $productData['base_price'],
                'category_id' => $productData['category_id'],
                'brand_id' => $productData['brand_id'],
                'status' => 'active',
                'thumbnail' => $thumbnailPath,
            ]);

            foreach ($variantsData as $vData) {
                $variant = $product->variants()->create([
                    'sku' => $vData['sku'],
                    'cost_price' => $vData['cost_price'] ?: 0,
                    'selling_price' => $vData['selling_price'],
                    'stock' => $vData['stock'],
                    'active' => $vData['active'],
                ]);

                if (isset($vData['values'])) {
                    $valIds = explode(',', $vData['values']);
                    $variant->attributeValues()->attach($valIds);
                }
            }

            return $product;
        });
    }

    public function updateProduct(Product $product, array $productData, array $variantsData, ?string $thumbnailPath): Product
    {
        return DB::transaction(function () use ($product, $productData, $variantsData, $thumbnailPath) {
            $data = [
                'name' => $productData['name'],
                'base_price' => $productData['base_price'],
                'category_id' => $productData['category_id'],
                'brand_id' => $productData['brand_id'],
            ];

            if ($thumbnailPath) {
                $data['thumbnail'] = $thumbnailPath;
            }

            $product->update($data);

            if (!empty($variantsData)) {
                foreach ($variantsData as $vData) {
                    if (isset($vData['id'])) {
                        $variant = $product->variants()->find($vData['id']);
                        if ($variant) {
                            $variant->update([
                                'sku' => $vData['sku'],
                                'cost_price' => $vData['cost_price'] ?: 0,
                                'selling_price' => $vData['selling_price'],
                                'stock' => $vData['stock'],
                                'active' => $vData['active'],
                            ]);
                        }
                    }
                }
            }
            
            return $product;
        });
    }

    public function adjustStock(int $variantId, string $type, int $qty, string $reason, int $userId): void
    {
        DB::transaction(function () use ($variantId, $type, $qty, $reason, $userId) {
            $variant = ProductVariant::where('id', $variantId)->lockForUpdate()->firstOrFail();
            
            $oldStock = $variant->stock;
            
            if ($type === 'out' && $variant->stock < $qty) {
                throw new Exception("Insufficient stock. Current stock is {$variant->stock}.");
            }
            
            $newStock = $type === 'in' ? $oldStock + $qty : $oldStock - $qty;
            
            $variant->stock = $newStock;
            $variant->save();
            
            StockMovement::create([
                'product_variant_id' => $variant->id,
                'user_id' => $userId,
                'type' => $type,
                'qty' => $qty,
                'balance_after' => $newStock,
                'reason' => $reason,
            ]);
        });
    }
}
