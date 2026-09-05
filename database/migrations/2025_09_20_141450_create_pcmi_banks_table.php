<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pcmi_banks', function (Blueprint $table) {
            $table->id();
            // FK to banks table (bank name comes from here)
            $table->foreignId('bank_id')->constrained('banks')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('type', ['Bank', 'Credit Card'])->default('Bank');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('account_no', 50);
            $table->string('branch')->nullable();
            $table->string('swift_code', 20)->nullable();
            $table->string('holder_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pcmi_banks');
    }
};
