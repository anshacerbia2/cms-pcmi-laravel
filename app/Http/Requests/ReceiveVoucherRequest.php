<?php

namespace App\Http\Requests;

class ReceiveVoucherRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }
 
    public function rules(): array
    {
        return [
            'rv_number' => 'required|string|unique:receive_vouchers,rv_number,' . $this->route('rv_id'),
            'rv_date' => 'required|date',
            'currency' => 'required|string',
            'currency_manual' => 'nullable|required_if:currency,Others|string',
            'amount' => 'required|numeric|min:0',
            'payment_form' => 'required|string|in:Cash,Bank,Credit Card',
            'pcmi_bank_id' => 'nullable|exists:pcmi_banks,id',
            'payment_form_value' => 'nullable|string',
            'payer_type' => 'required|string|in:Customer,Employee,Supplier,Others,Unknown',
            'payer_id' => 'nullable|integer',
            'payer_name_manual' => 'nullable|required_if:payer_type,Others|string',
            'purpose' => 'required|string',
            'payment_voucher_id' => 'nullable|exists:payment_vouchers,id',
            'invoice_ids' => 'nullable|array',
            'invoice_ids.*' => 'exists:invoices,id',
            'invoice_allocations' => 'nullable|array',
            'invoice_allocations.*.invoice_id' => 'required|exists:invoices,id',
            'invoice_allocations.*.amount_applied' => 'required|numeric|min:0',
            'invoice_allocations.*.ppn_wapu_deduction' => 'nullable|numeric|min:0',
            'invoice_allocations.*.pph23_deduction' => 'nullable|numeric|min:0',
            'invoice_allocations.*.bank_charge' => 'nullable|numeric|min:0',
            'invoice_allocations.*.others_adjustment' => 'nullable|numeric',
            'description' => 'nullable|string',
        ];
    }
}
