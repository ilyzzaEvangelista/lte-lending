<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_details', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('first_name', 60);
            $table->string('last_name', 60);
            $table->string('email', 60);
            $table->string('address', 255)->nullable();
            $table->decimal('amount', 14, 2);
            $table->decimal('interest', 8, 4)->default(0);
            $table->decimal('monthly', 14, 2)->nullable();
            $table->unsignedInteger('tenure');
            $table->string('purpose', 255)->nullable();
            $table->binary('payslip_image')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_details');
    }
};
