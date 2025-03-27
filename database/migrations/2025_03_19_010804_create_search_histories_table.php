<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id'); // Reference to the provider
            $table->string('search_query');
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('users_accounts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('search_histories');
    }
};
