<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->text('message')->nullable(); // الحقل الجديد
            $table->unsignedBigInteger('countries_id')->nullable(); // إضافة العمود country_code_id
            $table->timestamps();

            // إضافة الفهرس (index) على العمود country_code_id
            $table->foreign('countries_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

/**
 * Reverse the migrations.
 */
public function down(): void
{
    // Drop foreign key constraint
    Schema::table('contacts', function (Blueprint $table) {
        $table->dropForeign(['countries_id']);
    });

    // Drop the contacts table
    Schema::dropIfExists('contacts');
}

};
