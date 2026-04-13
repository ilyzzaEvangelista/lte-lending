<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_records', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 50);
            $table->foreign('transaction_no')->references('transaction_no')->on('loan_details')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->unsignedInteger('no_of_payments')->default(0);
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 14, 2);
            $table->decimal('interest', 8, 4)->default(0);
            $table->decimal('monthly_pay', 14, 2)->nullable();
            $table->decimal('payment', 14, 2)->default(0);
            $table->decimal('balance', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_records');
    }
};
