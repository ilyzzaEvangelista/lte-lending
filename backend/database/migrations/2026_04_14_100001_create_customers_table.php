<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('last_name', 60);
            $table->string('first_name', 60);
            $table->binary('profile_image')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('email', 60)->unique();
            $table->string('contact', 20)->nullable();
            $table->string('username', 60)->unique();
            $table->string('password');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
