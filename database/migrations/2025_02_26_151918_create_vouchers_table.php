<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users_accounts')->onDelete('cascade'); // ربط بالفئة المزودة
            $table->string('name');
            $table->decimal('amount', 10, 2); // قيمة القسيمة
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->date('start_date'); // تاريخ بداية القسيمة
            $table->integer('duration_days'); // مدة القسيمة بالأيام
            $table->string('random_num')->unique(); // رقم فريد لا يتكرر
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};