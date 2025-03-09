<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('voucher_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users_accounts')->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->timestamps();
    
            $table->unique(['user_id', 'voucher_id']); // يمنع التكرار لنفس المستخدم والفاتورة
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_favorites');
    }
};
