<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedBigInteger('subcategory_id');
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->text('description');
            $table->string('image');
            $table->decimal('old_price', 10, 2)->nullable();
            $table->decimal('new_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_id', 'subcategory_id', 'is_active']);
            $table->index(['subcategory_id', 'is_active', 'id']);
            $table->index(['is_active', 'id']);
            $table->foreign(['category_id', 'subcategory_id'], 'products_subcategory_parent_fk')
                ->references(['category_id', 'id'])->on('subcategories')
                ->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
