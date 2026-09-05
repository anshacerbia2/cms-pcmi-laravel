<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Invoice Code: I-000/Year/ActivityCode (Prefix automatic)
            $table->string('code')->unique();

            // Invoice No: P-000/ (Free text, Prefix automatic, Mandatory 3 digits)
            $table->string('invoice_number');

            // Due Date: Manual entry, Default 30 Days
            $table->date('due_date');

            // Sales Code: From DB Sales Code (Proposals or Projects)
            // Storing as string snapshot to handle both Fit and Regular flows + relationships
            $table->string('sales_code')->index()->nullable(); // Snapshot

            // Project Name (from Sales Code/Proposal)
            // Project Description (from Sales Code/Proposal)
            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->cascadeOnDelete();
                        
            $table->foreignId('proposal_id')
                ->nullable()
                ->constrained('proposals')
                ->cascadeOnDelete();

            // Billing To: From DB Customer
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('billing_option_id')
                ->nullable()
                ->constrained('billing_options')
                ->nullOnDelete();

            // Bank Account (Company Bank)
            // "KECUALI YANG NO TAX - Harus BCA yang XXX 666 tidak bisa yang lain"
            // Linking to pcmi_banks table
            $table->foreignId('pcmi_bank_id')
                ->nullable()
                ->constrained('pcmi_banks')
                ->nullOnDelete();
                
            $table->string('project_name')->nullable(); // Snapshot
            $table->text('project_description')->nullable(); // Snapshot

            $table->text('description')->nullable(); // Additional Description if needed

            // Type of billing
            $table->enum('billing_type', ['Partly Payment', 'Full Amount'])->default('Full Amount');

            // Taxation
            $table->enum('tax_type', ['No Tax', 'Tax - Non WAPU', 'Tax - WAPU'])->default('Tax - Non WAPU');

            // Invoice Amount
            $table->decimal('total_amount', 15, 2);
            $table->decimal('total_received_amount', 20, 2)->default(0);
            $table->decimal('balance_due', 20, 2)->default(0);
            $table->decimal('total_pph23_deduction', 20, 2)->default(0);
            $table->decimal('total_bank_charge', 20, 2)->default(0);

            // Invoice Status
            $table->enum('status', ['VOID', 'REVISED', 'PREPARED', 'SENT'])->default('PREPARED');

            // Payment Status
            $table->enum('payment_status', ['UNPAID', 'PARTLY PAID', 'FULLY PAID'])->default('UNPAID');

            // Kept for backward compatibility or calculation needs if acceptable, otherwise can be removed.
            // Leaving them for now as they might be useful for the logic:
            $table->enum('management_fee_type', ['nominal', 'percent'])->default('percent');
            $table->decimal('management_fee', 15, 2)->default(0);
            $table->integer('vat_rate')->default(11);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
