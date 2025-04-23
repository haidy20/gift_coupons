<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade'); // ربط الفاوتشر بالـ QR Code
            $table->foreignId('user_id')->constrained('users_accounts')->onDelete('cascade'); // ✅ ربط الـ QR Code بالمستخدم
            $table->foreignId('provider_id')->constrained('users_accounts')->onDelete('cascade'); // ✅ ربط الـ QR Code بالمستخدم
            $table->string('qr_code_path'); // مسار تخزين صورة QR Code
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('qr_codes');
    }
};
