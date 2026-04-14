<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_details', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('last_name');
            $table->unsignedTinyInteger('age')->nullable()->after('date_of_birth');
            $table->string('gender', 32)->nullable()->after('age');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('country', 100)->nullable()->after('city');
            $table->string('nearest_branch', 120)->nullable()->after('country');
            $table->string('phone', 30)->nullable()->after('nearest_branch');
            $table->string('profession', 120)->nullable()->after('phone');
            $table->string('loan_type', 120)->nullable()->after('profession');
            $table->string('monthly_income_other', 120)->nullable()->after('loan_type');
            $table->boolean('has_existing_loan')->default(false)->after('monthly_income_other');
            $table->timestamp('terms_accepted_at')->nullable()->after('has_existing_loan');
        });
    }

    public function down(): void
    {
        Schema::table('loan_details', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'age',
                'gender',
                'city',
                'country',
                'nearest_branch',
                'phone',
                'profession',
                'loan_type',
                'monthly_income_other',
                'has_existing_loan',
                'terms_accepted_at',
            ]);
        });
    }
};
