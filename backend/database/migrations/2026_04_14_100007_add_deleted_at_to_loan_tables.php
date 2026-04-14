<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_details', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('loan_records', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('loan_payments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('loan_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('loan_records', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('loan_payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
