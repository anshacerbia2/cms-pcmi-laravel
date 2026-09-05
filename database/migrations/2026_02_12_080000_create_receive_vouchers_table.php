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
        Schema::create('receive_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('rv_number')->unique();
            $table->date('rv_date');
            $table->enum('currency', ['IDR', 'USD', 'EUR', 'GBP', 'JPY', 'KRW', 'MYR', 'HKD', 'Others'])->default('IDR');
            $table->string('currency_manual')->nullable();
            $table->decimal('amount', 20, 2);
            $table->enum('payment_form', ['Bank', 'Credit Card', 'Cash']); // Cash, Bank, Credit Card
            
            // Link to our company banks
            $table->foreignId('pcmi_bank_id')->nullable()->constrained('pcmi_banks'); 
            $table->string('payment_form_value')->nullable();
            
            // Payer Information (Excel: Customer, Employee, Supplier, Others, Unknown)
            $table->enum('payer_type', ['Customer', 'Employee', 'Supplier', 'Others', 'Unknown']); 
            $table->unsignedBigInteger('payer_id')->nullable(); 
            $table->string('payer_name_manual')->nullable(); // For "Others" or "Unknown"
 
            // Purpose (Excel: Invoice, Return/Refund, Returning Deposit, Returning Cash Advance, Staff Loan, Others, Unknown)
            $table->enum('purpose', ['Invoice', 'Return/Refund', 'Returning Deposit', 'Returning Cash Advance', 'Staff Loan', 'Others', 'Unknown'])->default('Invoice');
            $table->foreignId('payment_voucher_id')->nullable()->constrained('payment_vouchers')->nullOnDelete();
            
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receive_vouchers');
    }
};
