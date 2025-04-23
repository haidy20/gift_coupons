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
        Schema::create('provider_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unique(['user_id', 'provider_id']); // منع التكرار
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users_accounts')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users_accounts')->onDelete('cascade');

        });


    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_favorites');
    }
};
