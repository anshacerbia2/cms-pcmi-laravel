<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'ktp_number',
        'sim_number',
        'passport_number',
        'npwp_number',
        'bpjs_number',
        'marital_status',
        'phone',
        'personal_email',
        'ktp_address',
        'ktp_province',
        'ktp_city',
        'ktp_district',
        'ktp_village',
        'current_address',
        'current_province',
        'current_city',
        'current_district',
        'current_village',
        'join_date',
        'position',
        'bank_id',
        'bank_account_number',
        'bank_account_name',
        'username',
        'password',
        'role',
        'ktp_path',
        'sim_path',
        'passport_path',
        'bpjs_path',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    public static function generateCode(): string
    {
        $date = \Carbon\Carbon::now()->format('Ymd');
        do {
            $random = strtoupper(\Illuminate\Support\Str::random(5));
            $code = "EMP-{$date}-{$random}";
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
