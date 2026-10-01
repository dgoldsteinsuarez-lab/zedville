<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** No tracked migration existed — discovered testing the Supermarket flow. */
    public function up()
    {
        Schema::create('supermarkets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sid')->nullable();
            $table->string('product_name');
            $table->string('type')->nullable();
            $table->string('category')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        DB::table('supermarkets')->insert([
            ['product_name' => 'Bread', 'type' => 'groceries', 'category' => 'Needs', 'price' => 3.50, 'quantity' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['product_name' => 'Milk', 'type' => 'groceries', 'category' => 'Needs', 'price' => 2.20, 'quantity' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['product_name' => 'Eggs', 'type' => 'groceries', 'category' => 'Needs', 'price' => 4.00, 'quantity' => 100, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('supermarkets');
    }
};
