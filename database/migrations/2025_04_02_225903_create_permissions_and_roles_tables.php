<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration

{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('front_route_name')->nullable();
            $table->string('back_route_name')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_control_permission')->default(0)->nullable();
            $table->timestamps();
        });

        Schema::create('permission_translations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('locale')->index();
            $table->foreignId('permission_id', 'permission_id')->constrained("permissions")->cascadeOnDelete();
            $table->unique(['permission_id', 'locale'], 'permission_id');
        });
        
        // Povit Table
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->unsignedBigInteger('permission_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permission_translations');
        Schema::dropIfExists('permissions');
    }
};
