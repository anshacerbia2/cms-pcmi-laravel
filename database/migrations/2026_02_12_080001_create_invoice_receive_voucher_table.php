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
        Schema::create('invoice_receive_voucher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('receive_voucher_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_applied', 20, 2);
            $table->decimal('ppn_wapu_deduction', 20, 2)->default(0);
            $table->decimal('pph23_deduction', 20, 2)->default(0);
            $table->decimal('bank_charge', 20, 2)->default(0);
            $table->decimal('others_adjustment', 20, 2)->default(0);
            $table->string('adjustment_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_receive_voucher');
    }
};
