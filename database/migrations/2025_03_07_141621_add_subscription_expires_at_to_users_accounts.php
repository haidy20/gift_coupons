<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users_accounts', function (Blueprint $table) {
            $table->dateTime('subscription_expires_at')->nullable()->after('subscription_id');
        });
    }
    
    public function down()
    {
        Schema::table('users_accounts', function (Blueprint $table) {
            $table->dropColumn('subscription_expires_at');
        });
    }
    
};
