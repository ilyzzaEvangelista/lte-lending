<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 50);
            $table->foreign('transaction_no')->references('transaction_no')->on('loan_details')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('full_name', 120);
            $table->decimal('amount', 14, 2);
            $table->decimal('balance', 14, 2)->nullable();
            $table->binary('receipt_image')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('confirmed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
