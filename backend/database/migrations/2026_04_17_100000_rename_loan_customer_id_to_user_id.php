<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Loan tables reference the borrower account on `users` (role client).
     * Rename legacy `customer_id` to `user_id` so the FK is explicit.
     */
    public function up(): void
    {
        foreach (['loan_payments', 'loan_records', 'loan_details'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'customer_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['customer_id']);
            });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->renameColumn('customer_id', 'user_id');
            });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['loan_payments', 'loan_records', 'loan_details'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['user_id']);
            });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->renameColumn('user_id', 'customer_id');
            });

            if (Schema::hasTable('customers')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
                });
            } else {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreign('customer_id')->references('id')->on('users')->cascadeOnDelete();
                });
            }
        }
    }
};
