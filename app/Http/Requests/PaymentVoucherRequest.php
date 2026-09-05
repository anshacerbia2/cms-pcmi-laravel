<?php

namespace App\Http\Requests;

class PaymentVoucherRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pv_number' => 'required|string|unique:payment_vouchers,pv_number,' . $this->route('pv_id'),
            'issuing_date' => 'required|date',
            'due_date' => 'nullable|date',
            'currency' => 'required|string|max:3',
            'amount' => 'required|numeric|min:0',
            'payable_type' => 'required|string',
            'payable_id' => 'nullable|integer',
            'payable_name_manual' => 'nullable|string',
            'category' => 'required|string',
            'purchase_order_id' => 'nullable|integer',
            'expense_type' => 'nullable|string',
            'description' => 'nullable|string',
            'source_payment_form' => 'required|string',
            'pcmi_bank_id' => 'nullable|exists:pcmi_banks,id',
            'payment_date' => 'nullable|date',
        ];
    }
}
