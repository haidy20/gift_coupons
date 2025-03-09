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
        Schema::create('about_us_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('about_id');
            $table->foreign('about_id')->references('id')->on('about_us')->onDelete('cascade');

            $table->string('locale')->index(); //en , ar
            $table->string('title');
            $table->text('description');
            $table->unique(['about_id', 'locale']); // لمنع التكرار
            $table->timestamps(); // ✅ إضافة created_at و updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us_translations');
    }
};
