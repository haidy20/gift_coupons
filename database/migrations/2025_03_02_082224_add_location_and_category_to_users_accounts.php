<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users_accounts', function (Blueprint $table) {
            $table->decimal('lat', 10, 8)->nullable()->after('countries_id'); // خط العرض
            $table->decimal('long', 11, 8)->nullable()->after('lat'); // خط الطول
            $table->string('location')->nullable()->after('long'); // الموقع كنص

            $table->unsignedBigInteger('category_id')->nullable()->after('location');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users_accounts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['lat', 'long', 'location', 'category_id']);
        });
    }
};
