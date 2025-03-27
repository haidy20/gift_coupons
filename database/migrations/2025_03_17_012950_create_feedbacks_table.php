<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users_accounts')->onDelete('cascade'); // علاقة بالمستخدم
            $table->integer('rating'); // عدد النجوم من 1 إلى 5
            $table->text('description'); // نص التقييم مباشرةً
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('feedbacks');
    }
};
