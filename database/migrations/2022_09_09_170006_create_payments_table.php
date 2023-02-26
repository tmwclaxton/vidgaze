<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');;
//            $table->foreignId('product_id')->references('id')->on('products');;
            $table->string('name')->nullable();
            $table->string('stripe_id')->nullable();
            $table->string('receipt_url');
            $table->integer('subtotal');
            $table->integer('tax')->default(0);
            $table->integer('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
