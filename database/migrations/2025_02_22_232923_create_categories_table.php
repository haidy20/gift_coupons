<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id('id');  // Primary key
            $table->string('category_name');  // Category name
            // $table->text('description')->nullable();  // Description
            $table->timestamps();  // Timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};