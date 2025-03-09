<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->string('locale')->index(); // en, ar
            $table->string('title'); // ✅ العنوان
            $table->text('description')->nullable(); // ✅ الوصف

            $table->decimal('price', 10, 2); // ✅ السعر حسب اللغة
            $table->string('duration'); // ✅ مدة الاشتراك (مثلاً "شهر واحد" بدلاً من رقم)

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            $table->unique(['subscription_id', 'locale']); // ✅ منع تكرار نفس اللغة للاشتراك
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_translations');
    }
};
