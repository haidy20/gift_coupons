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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->enum('transaction_type', ['deposit', 'withdrawal']);
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('checkout_id')->nullable();
            $table->decimal('amount', 10, 2);


            $table->timestamps();
            $table->foreign('provider_id')->references('id')->on('users_accounts')->onDelete('cascade');
            $table->foreign('checkout_id')->references('id')->on('checkouts')->onDelete('cascade'); // تحديد العلاقة
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['checkout_id']);
            $table->dropColumn('checkout_id');
        });
        Schema::dropIfExists('transactions');

    }
};
