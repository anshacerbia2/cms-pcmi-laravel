<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiveVoucher extends Model
{
    protected $fillable = [
        'rv_number',
        'rv_date',
        'currency',
        'currency_manual',
        'amount',
        'payment_form',
        'pcmi_bank_id',
        'payment_form_value',
        'payer_type',
        'payer_id',
        'payer_name_manual',
        'purpose',
        'payment_voucher_id',
        'description',
    ];

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_receive_voucher')
            ->withPivot(['amount_applied', 'ppn_wapu_deduction', 'pph23_deduction', 'bank_charge', 'others_adjustment', 'adjustment_description'])
            ->withTimestamps();
    }

    public function pcmiBank()
    {
        return $this->belongsTo(PcmiBank::class);
    }

    public function paymentVoucher()
    {
        return $this->belongsTo(PaymentVoucher::class);
    }
}
