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
        Schema::table('provider_favorites', function (Blueprint $table) {
            $table->string('type')->default('provider'); // Add default value if needed
        });
    }
    
    public function down()
    {
        Schema::table('provider_favorites', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
    
};
