<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('id');

            $table->unsignedBigInteger('cart_id')->index();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();

            $table->unsignedBigInteger('product_vendor_sku_id')->nullable();
            $table->unsignedBigInteger('product_vendor_sku_unit_id')->nullable();

            $table->unsignedBigInteger('unit_id')->nullable();

            $table->string('sku', 191)->nullable();
            $table->integer('package_size')->default(1);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index([
                'cart_id',
                'product_id',
                'product_vendor_sku_id',
                'product_vendor_sku_unit_id',
            ], 'cart_items_match_index');

            $table->foreign('cart_id', 'cart_items_cart_id_fk')
                ->references('id')->on('carts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('product_id', 'cart_items_product_id_fk')
                ->references('id')->on('products')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('product_vendor_sku_id', 'cart_items_pvsku_id_fk')
                ->references('id')->on('product_vendor_skus')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('product_vendor_sku_unit_id', 'cart_items_pvsku_unit_id_fk')
                ->references('id')->on('product_vendor_sku_units')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
