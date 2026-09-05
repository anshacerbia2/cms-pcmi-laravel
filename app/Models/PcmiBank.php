<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PcmiBank extends Model
{
    protected $fillable = [
        'bank_id',
        'type', // Bank, Credit Card
        'user_id', // For Credit Card holder
        'account_no',
        'branch',
        'swift_code',
        'holder_name',
    ];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
