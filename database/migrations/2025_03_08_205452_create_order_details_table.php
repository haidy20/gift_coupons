<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->timestamps(); // يحتوي على created_at و updated_at

            $table->foreign('order_id')->references('id')->on('checkouts')->onDelete('cascade');
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('order_details');
    }
};
