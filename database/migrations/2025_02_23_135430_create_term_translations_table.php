<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('term_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->string('locale')->index(); //en , ar
            $table->string('title');
            $table->text('description');
            $table->unique(['term_id', 'locale']); // لمنع التكرار
            $table->timestamps(); // ✅ إضافة created_at و updated_at

        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('term_translations');
    }
};

