<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null')->after('category_id');
            $table->decimal('base_price', 10, 2)->after('brand_id');
            $table->string('status')->default('draft')->after('base_price');
            $table->string('thumbnail')->nullable()->after('status');
            
            $table->dropColumn('price');
            $table->dropColumn('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['slug', 'brand_id', 'base_price', 'status', 'thumbnail']);
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
        });
    }
};
