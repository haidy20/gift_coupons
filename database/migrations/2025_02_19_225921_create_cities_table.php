<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();  // auto-increment primary key
            $table->unsignedBigInteger('countries_id');  // Foreign key column
            $table->string('city_name');  // Name of the city
            $table->timestamps();  // Created at and Updated at timestamps
            
            // Define the foreign key relationship
            $table->foreign('countries_id')
                  ->references('id')  // References the id column in countries_codes
                  ->on('countries')  // On the countries_codes table
                  ->onDelete('cascade');  // Define what happens on delete (cascade is optional, you can adjust)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
