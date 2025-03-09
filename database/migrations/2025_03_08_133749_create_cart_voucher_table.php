<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cart_voucher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->integer('quantity')->default(0); // تحديد عدد الفاوتشرات داخل كل كارت
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cart_voucher');
    }
};
