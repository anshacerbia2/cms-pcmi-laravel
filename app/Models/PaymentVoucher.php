<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentVoucher extends Model
{
    protected $fillable = [
        'pv_number',
        'issuing_date',
        'due_date',
        'currency',
        'amount',
        'payable_type',
        'payable_id',
        'payable_name_manual',
        'category',
        'purchase_order_id',
        'expense_type',
        'description',
        'source_payment_form',
        'pcmi_bank_id',
        'payment_date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function pcmiBank()
    {
        return $this->belongsTo(PcmiBank::class);
    }
}
