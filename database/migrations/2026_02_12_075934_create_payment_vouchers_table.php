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
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('pv_number')->unique();
            $table->date('issuing_date');
            $table->date('due_date')->nullable();
            $table->string('currency')->default('IDR');
            $table->decimal('amount', 20, 2);
            
            // Payable to (Excel: Employee, Supplier, Internal, Others)
            $table->string('payable_type'); 
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->string('payable_name_manual')->nullable();
            
            // Category (Excel: COGS/AP Trade, Expense, Staff Loan)
            $table->string('category'); 
            
            // If COGS, link to PO (Table not yet created, leaving as unconstrained ID for now)
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            
            // If Expense, we might have a specific sub-category
            $table->string('expense_type')->nullable(); // e.g. Meals, BPJS, etc.
            
            $table->text('description')->nullable();
            
            $table->string('source_payment_form'); // Bank, Cash, Credit Card
            $table->foreignId('pcmi_bank_id')->nullable()->constrained('pcmi_banks'); 
            $table->date('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_vouchers');
    }
};
