<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lot_number', 100);
            $table->date('expires_at')->nullable();
            $table->unsignedInteger('initial_quantity');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['product_id', 'expires_at']);
            $table->index(['lot_number']);
        });

        Schema::create('sale_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['sale_item_id', 'stock_batch_id']);
        });

        DB::table('products')
            ->where('stock_quantity', '>', 0)
            ->orderBy('id')
            ->get(['id', 'reference', 'stock_quantity', 'purchase_price', 'created_at', 'updated_at'])
            ->each(function ($product) {
                DB::table('stock_batches')->insert([
                    'product_id' => $product->id,
                    'lot_number' => 'INITIAL-'.$product->reference,
                    'initial_quantity' => $product->stock_quantity,
                    'quantity' => $product->stock_quantity,
                    'unit_cost' => $product->purchase_price,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_batches');
        Schema::dropIfExists('stock_batches');
    }
};
