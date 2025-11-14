<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // como tu login usa username:
            $table->string('username')->unique();

            // si quieres email opcional:
            $table->string('email')->nullable()->unique();

            $table->string('password');

            // tu sistema usa roles con id_rol
            $table->unsignedBigInteger('id_rol')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
