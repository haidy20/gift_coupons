<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_vouchers', function (Blueprint $table) {
            $table->id(); // `id` INT PRIMARY KEY AUTO_INCREMENT
            $table->foreignId('user_id')->constrained('users_accounts')->onDelete('cascade'); // العميل الذي اشترى القسيمة
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade'); // القسيمة المرتبطة
            $table->foreignId('provider_id')->constrained('users_accounts')->onDelete('cascade'); // البروفايدر المسؤول عن القسيمة
            $table->dateTime('purchase_date'); // وقت شراء القسيمة
            $table->dateTime('expiry_date'); // وقت انتهاء القسيمة
            $table->dateTime('used_date')->nullable(); // متى تم استخدامها (NULL لو لم تستخدم بعد)
            $table->enum('status', ['active', 'used', 'expired'])->default('active'); // حالة القسيمة
            $table->timestamps(); // `created_at` و `updated_at`
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_vouchers');
    }
};
