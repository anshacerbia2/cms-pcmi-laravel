<?php

namespace App\Http\Services;

use App\Models\PaymentVoucher;

class PaymentVoucherService
{
    public function getAll()
    {
        return PaymentVoucher::with(['pcmiBank.bank'])->get();
    }

    public function getById($id)
    {
        return PaymentVoucher::with(['pcmiBank.bank'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return PaymentVoucher::create($data);
    }

    public function update($id, array $data)
    {
        $pv = PaymentVoucher::findOrFail($id);
        $pv->update($data);
        return $pv;
    }

    public function delete($id)
    {
        $pv = PaymentVoucher::findOrFail($id);
        return $pv->delete();
    }
}
