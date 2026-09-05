<?php

namespace App\Http\Services;

use App\Models\ReceiveVoucher;

class ReceiveVoucherService
{
    public function getAll()
    {
        return ReceiveVoucher::with(['pcmiBank.bank'])->get();
    }

    public function getById($id)
    {
        return ReceiveVoucher::with(['pcmiBank.bank', 'invoices', 'paymentVoucher'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return \DB::transaction(function () use ($data) {
            $rv = ReceiveVoucher::create($data);
            
            if (isset($data['invoice_allocations']) && count($data['invoice_allocations']) > 0) {
                $this->syncInvoices($rv, $data['invoice_allocations'], (float)$data['amount']);
            }
            
            return $rv;
        });
    }

    public function update($id, array $data)
    {
        return \DB::transaction(function () use ($id, $data) {
            $rv = ReceiveVoucher::findOrFail($id);
            $rv->update($data);
            
            if (isset($data['invoice_allocations'])) {
                $this->syncInvoices($rv, $data['invoice_allocations'], (float)$data['amount']);
            } else {
                if ($data['purpose'] !== 'Invoice') {
                    $oldInvoices = $rv->invoices;
                    $rv->invoices()->detach();
                    foreach($oldInvoices as $inv) {
                        $inv->recalculateReconciliation();
                    }
                }
            }
            return $rv;
        });
    }

    protected function syncInvoices($rv, array $allocations, float $totalRvAmount)
    {
        $syncData = [];
        $totalApplied = 0;

        foreach ($allocations as $allocation) {
            $invoiceId = $allocation['invoice_id'];
            $ppn_wapu = (float)($allocation['ppn_wapu_deduction'] ?? 0);
            $pph23 = (float)($allocation['pph23_deduction'] ?? 0);
            $bank_charge = (float)($allocation['bank_charge'] ?? 0);
            $adjustment = (float)($allocation['others_adjustment'] ?? 0);

            // Save if there's any value (applied cash OR any deduction/adjustment)
            if ($applied == 0 && $ppn_wapu == 0 && $pph23 == 0 && $bank_charge == 0 && $adjustment == 0) {
                continue;
            }

            $invoice = \App\Models\Invoice::findOrFail($invoiceId);
            
            // Validate: cannot pay more than current balance due
            // For updates, we need to consider the current applied amount already exists in pivot
            $currentAppliedInPivot = $rv->invoices()->where('invoice_id', $invoiceId)->first()?->pivot?->amount_applied ?? 0;
            $maxAllowed = $invoice->balance_due + $currentAppliedInPivot;

            if ($applied > round($maxAllowed, 2)) {
                throw new \Exception("Alokasi untuk Invoice {$invoice->code} ({$applied}) melebihi sisa tagihan (" . round($maxAllowed, 2) . ").");
            }

            $syncData[$invoiceId] = [
                'amount_applied'         => $applied,
                'ppn_wapu_deduction'     => (float)($allocation['ppn_wapu_deduction'] ?? 0),
                'pph23_deduction'        => (float)($allocation['pph23_deduction'] ?? 0),
                'bank_charge'            => (float)($allocation['bank_charge'] ?? 0),
                'others_adjustment'      => (float)($allocation['others_adjustment'] ?? 0),
                'adjustment_description' => $allocation['adjustment_description'] ?? null,
            ];

            $totalApplied += $applied;
        }

        // Validate: Sum of applied amounts should not exceed RV amount
        if (round($totalApplied, 2) > round($totalRvAmount, 2)) {
            throw new \Exception("Total alokasi (" . round($totalApplied, 2) . ") melebihi nilai Receive Voucher (" . round($totalRvAmount, 2) . ").");
        }

        // Perform Sync
        $rv->invoices()->sync($syncData);

        // Reconciliation
        $affectedInvoiceIds = array_keys($syncData);
        $invoicesToUpdate = \App\Models\Invoice::whereIn('id', $affectedInvoiceIds)->get();
        foreach ($invoicesToUpdate as $inv) {
            $inv->recalculateReconciliation();
        }
    }

    public function delete($id)
    {
        $rv = ReceiveVoucher::findOrFail($id);
        return $rv->delete();
    }
}
