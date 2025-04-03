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
        
        Schema::create('users_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('countries_id')->nullable();
            $table->string('username');
            $table->string('email')->nullable()->unique();  // السماح بحقل email فارغ (nullable)
            $table->string('password');
            $table->string('phone')->unique();
            $table->enum('role', ['client', 'provider', 'admin'])->default('client');

            $table->boolean('is_active')->default(false); // Adding the is_active column with a default value of true
            $table->string('reset_code')->nullable(); // Add the column
            $table->timestamps();
            
            // (foreign key)
            $table->foreign('countries_id')->references('id')->on('countries')->onDelete('cascade');

        });
    }

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_accounts', function (Blueprint $table) {
            $table->dropColumn('reset_code'); // Drop the column if rolling back
        });
        Schema::dropIfExists('users_accounts');

    
    }
};