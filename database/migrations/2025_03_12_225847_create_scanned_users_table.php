<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('scanned_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users_accounts')->onDelete('cascade'); // المستخدم الذي حصل على الفاوتشر
            $table->foreignId('provider_id')->constrained('users_accounts')->onDelete('cascade'); // البروفايدر الذي قام بمسح الكود
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade'); // الفاوتشر نفسه
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scanned_users');
    }
};
