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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // 1. Employee Code
            $table->string('code')->unique()->index();
            
            // 2. Name
            $table->string('name')->index();
            
            // 3. Identitas (KTP, SIM, Passport, NPWP, BPJS)
            $table->string('ktp_number', 16)->nullable()->unique();
            $table->string('sim_number')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('npwp_number')->nullable();
            $table->string('bpjs_number')->nullable();
            
            // 8. Status (TK, K1, K2, K3)
            $table->enum('marital_status', ['TK', 'K1', 'K2', 'K3'])->default('TK');
            
            // 9-10. Kontak
            $table->string('phone')->nullable();
            $table->string('personal_email')->nullable();
            
            // 11. Alamat Sesuai KTP
            $table->text('ktp_address')->nullable();
            $table->string('ktp_province')->nullable();
            $table->string('ktp_city')->nullable();
            $table->string('ktp_district')->nullable();
            $table->string('ktp_village')->nullable();
            
            // 12. Alamat Tempat Tinggal
            $table->text('current_address')->nullable();
            $table->string('current_province')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_district')->nullable();
            $table->string('current_village')->nullable();
            
            // 13. Tanggal Masuk Kerja
            $table->date('join_date')->nullable();
            
            // 14. Jabatan
            $table->string('position')->nullable()->index();
            
            // 15. Bank Account (Linked to banks master table)
            $table->foreignId('bank_id')
                ->nullable()
                ->constrained('banks')
                ->onDelete('set null');
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            
            // 16-18. Sistem (Standalone for now)
            $table->string('username')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('role')->nullable(); // Authorization
            
            // File Upload Paths (Note 1)
            $table->string('ktp_path')->nullable();
            $table->string('sim_path')->nullable();
            $table->string('passport_path')->nullable();
            $table->string('bpjs_path')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
