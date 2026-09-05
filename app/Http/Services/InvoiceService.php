<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\Customer;
use App\Models\Boq;
use App\Models\Project;
use App\Models\SalesItem;
use Illuminate\Support\Facades\DB;
use Exception;

class InvoiceService
{
    public function createInvoice(array $data)
    {
        return DB::transaction(function () use ( $data) {
            // Determine Flow based on Project Type
            $project = null;
            if (isset($data['project_id'])) {
                $project = Project::find($data['project_id']);
            }

            // ------------------------------ FIT Project Flow ------------------------------
            if ($project && $project->type === 'FIT') {
                $invoiceCode = Invoice::generateCodeFromProject($project);

                $customer = Customer::find($data['customer_id']);
                if (!$customer) {
                    throw new Exception("Customer with ID {$data['customer_id']} not found.");
                }

                $invoice = Invoice::create([
                    'project_id'          => $data['project_id'],
                    'proposal_id'         => null,
                    'customer_id'         => $data['customer_id'],
                    'billing_option_id'   => $data['billing_option_id'],
                    'pcmi_bank_id'        => $data['pcmi_bank_id'],
                    'code'                => $invoiceCode,
                    'invoice_number'      => $data['invoice_number'],
                    'due_date'            => $data['due_date'],
                    'sales_code'          => $project->sales_code,
                    'project_name'        => $project->name,
                    'project_description' => $project->description,
                    'description'         => $data['description'],
                    'billing_type'        => $data['billing_type'],
                    'tax_type'            => $data['tax_type'],
                    'total_amount'        => $data['total_amount'],
                    'status'              => $data['status'],
                    'payment_status'      => $data['payment_status'],
                    'management_fee_type' => $data['management_fee_type'],
                    'management_fee'      => $data['management_fee'],
                    'vat_rate'            => $data['vat_rate'],
                ]);

                // Create Single Sales Item for FIT Project (Derived from Invoice Data)
                SalesItem::create([
                    'project_id'   => $project->id,
                    'proposal_id'  => null,
                    'invoice_id'   => $invoice->id,
                    'description'  => $data['description'],
                    'selling_price'=> $data['total_amount'],
                    'total_price'  => $data['total_amount'],
                    'title1_key'   => 'Qty',
                    'title1_value' => 1,
                ]);

                return $invoice->fresh(['project', 'proposal.project', 'proposal.items', 'customer', 'pcmiBank.bank', 'items.product']);
            }

            // ------------------------------ Regular Project Flow ------------------------------
            // Fallback for Regular Projects (or if project_id is missing, assuming proposal-based)
            if (!isset($data['proposal_id'])) {
                 throw new Exception("Proposal ID or a valid FIT Project ID is required.");
            }

            $proposal = Proposal::with(['project', 'items'])->find($data['proposal_id']);

            if (!$proposal) {
                throw new Exception("Proposal with ID {$data['proposal_id']} not found.");
            }
            
            // Validate that the project is NOT FIT (Regular projects only)
            if ($proposal->project && $proposal->project->type !== 'Regular') {
                 throw new Exception("Only Regular Projects can generate invoices via Proposals. Type is '{$proposal->project->type}'.");
            }
            
            if ($proposal->status !== 'Win') {
                throw new Exception("Invoice can only be generated for win proposals.");
            }
            
            if (!$proposal->pricing_model) {
                throw new Exception("Proposal must have a pricing model configured to generate an invoice.");
            }
            
            // Check if billing_type 'Full Amount' is allowed (must be the only invoice)
            if ($data['billing_type'] === 'Full Amount') {
                $otherInvoicesCount = $proposal->invoices()
                    ->count();

                if ($otherInvoicesCount > 0) {
                     // If there are other existing invoices, type cannot be Full
                    throw new Exception("Cannot create a 'Full' invoice because other invoices already exist for this proposal.");
                }

                // Verify ALL proposal items are available (invoice_id is null)
                $unavailableItems = $proposal->items->whereNotNull('invoice_id')->count();
                if ($unavailableItems > 0) {
                    throw new Exception("Cannot create a 'Full' invoice because some items are already invoiced.");
                }

                // If Full, force select ALL proposal items
                $data['item_ids'] = $proposal->items->pluck('id')->toArray();
            } 

            // Get only items from proposal that haven't been invoiced
            $availableItemIds = $proposal->items
                ->whereNull('invoice_id')
                ->pluck('id')
                ->toArray();

            if (empty($availableItemIds)) {
                throw new Exception("No available items to be billed for this proposal.");
            }
         
            // Ensure all selected items are valid
            if (array_diff($data['item_ids'], $availableItemIds)) {
                throw new Exception("Some selected items are not available for invoicing in this proposal.");
            }

            $customer = Customer::find($data['customer_id']);
            
            if (!$customer) {
                throw new Exception("Customer with ID {$data['customer_id']} not found.");
            }
            
            // Generate invoice code for Regular (via Invoice model)
            $invoiceCode = Invoice::generateCode($proposal); 

            // Calculate amounts from selected items and proposal
            $selectedItems = $proposal->items->whereIn('id', $data['item_ids']);
            $totalAmount = $selectedItems->sum('total_price');
            
            // Calculate management fee value to store
            $managementFeeToStore = $this->calculateProposalFeeValue($proposal, $totalAmount);

            $invoice = Invoice::create([
                'project_id'          => $proposal->project_id, // Link to project via proposal
                'proposal_id'         => $data['proposal_id'],
                'customer_id'         => $data['customer_id'],
                'billing_option_id'   => $data['billing_option_id'],
                'pcmi_bank_id'        => $data['pcmi_bank_id'],
                'code'                => $invoiceCode,
                'invoice_number'      => $data['invoice_number'],
                'due_date'            => $data['due_date'],
                'sales_code'          => $proposal->sales_code,
                'project_name'        => $proposal->project->name,
                'project_description' => $proposal->project->description,
                'description'         => $data['description'] ?? null,
                'billing_type'        => $data['billing_type'],
                'tax_type'            => $data['tax_type'],
                'total_amount'        => $totalAmount,
                'status'              => $data['status'],
                'payment_status'      => $data['payment_status'],
                'management_fee_type' => $proposal->management_fee_type,
                'management_fee'      => $managementFeeToStore,
                'vat_rate'            => $proposal->vat_rate,
            ]);
            
            // Link items to invoice
            SalesItem::whereIn('id', $data['item_ids'])
                ->update(['invoice_id' => $invoice->id]);

            return $invoice->fresh(['project', 'proposal.project', 'proposal.items', 'customer', 'pcmiBank.bank', 'items.product']);
        });
    }

    
    public function getInvoiceById($id)
    {
        $invoice = Invoice::with(['project', 'proposal.project', 'proposal.items', 'customer', 'pcmiBank.bank', 'items.product'])->find($id);
        if (!$invoice) {
            throw new Exception("Invoice with ID {$id} not found");
        }
        return $invoice;
    }

    public function getUnpaidInvoices($customerId = null)
    {
        $query = Invoice::where('payment_status', '!=', 'FULLY PAID');
            // ->whereNotIn('status', ['VOID', 'REVISED'])
            // ->where('balance_due', '>', 0);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        return $query->with(['customer', 'proposal', 'project'])->get();
    }

    public function updateInvoice(array $data)
    {
        return DB::transaction(function () use ( $data) {
            $invoice = Invoice::withCount('receiveVouchers')->find($data['id']);

            if (!$invoice) {
                throw new Exception("Invoice with ID {$data['id']} not found.");
            }

            // --- DATA INTEGRITY GUARD ---
            if ($invoice->receive_vouchers_count > 0) {
                $sensitiveFields = ['tax_type', 'total_amount', 'customer_id'];
                foreach ($sensitiveFields as $field) {
                    if (isset($data[$field]) && $data[$field] != $invoice->$field) {
                        throw new Exception("Cannot change sensitive field '{$field}' because this invoice already has linked Receive Vouchers. Please delete the RV first if you need to reconfigure the tax/amount.");
                    }
                }
            }

            // ------------------------------ FIT Project Flow ------------------------------
            // Use strict type check to determine flow
            if ($invoice->project && $invoice->project->type === 'FIT') {
                /* For FIT, we expect financial fields to be passed, but we trust the Model accessors for amounts.
                 * Just update the base values.
                 */
                $invoice->update([
                    'invoice_number'      => $data['invoice_number'],
                    'billing_option_id'   => $data['billing_option_id'],
                    'pcmi_bank_id'        => $data['pcmi_bank_id'],
                    'due_date'            => $data['due_date'],
                    'description'         => $data['description'] ?? null,
                    'billing_type'        => $data['billing_type'],
                    'tax_type'            => $data['tax_type'],
                    'total_amount'        => $data['total_amount'],
                    'status'              => $data['status'],
                    'payment_status'      => $data['payment_status'],
                    'management_fee_type' => $data['management_fee_type'],
                    'management_fee'      => $data['management_fee'],
                    'vat_rate'            => $data['vat_rate'],
                ]);

                // Sync Sales Items (Delete Old -> Create New Single Item)
                SalesItem::where('invoice_id', $invoice->id)->delete();

                SalesItem::create([
                    'project_id'   => $invoice->project_id,
                    'proposal_id'  => null,
                    'invoice_id'   => $invoice->id,
                    'description'  => $data['description'],
                    'selling_price'=> $data['total_amount'],
                    'total_price'  => $data['total_amount'],
                    'title1_key'   => 'Qty',
                    'title1_value' => 1,
                ]);

                return $invoice->fresh(['project', 'proposal.project', 'proposal.items', 'customer', 'pcmiBank.bank', 'items.product']);
            }

            // ------------------------------ Regular Project Flow ------------------------------
            // Fallback for everything else (Regular)
            $proposalId = $invoice->proposal_id;
            $proposal = Proposal::with(['items'])->find($proposalId);

            if (!$proposal) {
                throw new Exception("Proposal with ID {$proposalId} not found.");
            }
            
            if ($proposal->status !== 'Win') {
                throw new Exception("Invoice can only be edited for win proposals.");
            }

            if (!$proposal->pricing_model) {
                throw new Exception("Proposal must have a pricing model configured to generate an invoice.");
            }
            
            // Invoice Type Validation. Check if billing_type 'Full Amount' is allowed
            if ($data['billing_type'] === 'Full Amount') {
                $otherInvoicesCount = $proposal->invoices()
                    ->where('id', '!=', $invoice->id) // Exclude current invoice
                    ->count();

                if ($otherInvoicesCount > 0) {
                     // If there are other existing invoices, type cannot be Full
                    throw new Exception("Cannot set invoice type to 'Full' because other invoices already exist for this proposal.");
                }

                // Verify ALL proposal items are available (invoice_id is null OR owned by current invoice)
                $unavailableItems = $proposal->items
                    ->filter(fn($b) => $b->invoice_id !== null && $b->invoice_id !== $invoice->id)
                    ->count();

                if ($unavailableItems > 0) {
                    throw new Exception("Cannot set 'Full' type because some items are billed in other invoices.");
                }

                // If type is Full, force select ALL proposal items
                $data['item_ids'] = $proposal->items->pluck('id')->toArray();
            }

            // Get only items from proposal that haven't been invoiced
            $availableItemIds = $proposal->items
                ->filter(fn($item) => !$item->invoice_id || $item->invoice_id === $invoice->id)
                ->pluck('id')
                ->toArray();

            if (empty($availableItemIds)) {
                throw new Exception("No available items to be billed for this proposal.");
            }
         
             // Make sure all selected items are available
            if (array_diff($data['item_ids'], $availableItemIds)) {
                throw new Exception("Some selected items are not available for invoicing in this proposal.");
            } 

            $customer = Customer::find($data['customer_id']);
            
            if (!$customer) {
                throw new Exception("Customer with ID {$data['customer_id']} not found.");
            }
            
            // Calculate amounts from selected items and proposal
            $selectedItems = $proposal->items->whereIn('id', $data['item_ids']);
            $totalAmount = $selectedItems->sum('total_price');
            
            // Calculate management fee value to store
            $managementFeeToStore = $this->calculateProposalFeeValue($proposal, $totalAmount);
            
            $invoice->update([
                'invoice_number'      => $data['invoice_number'],
                'billing_option_id'   => $data['billing_option_id'],
                'pcmi_bank_id'        => $data['pcmi_bank_id'],
                'due_date'            => $data['due_date'],
                'description'         => $data['description'] ?? null,
                'billing_type'        => $data['billing_type'],
                'tax_type'            => $data['tax_type'],
                'total_amount'        => $totalAmount,
                'status'              => $data['status'],
                'payment_status'      => $data['payment_status'],
                'management_fee_type' => $proposal->management_fee_type,
                'management_fee'      => $managementFeeToStore,
                'vat_rate'            => $proposal->vat_rate,
            ]);
            
            // Reset old items
            SalesItem::where('invoice_id', $invoice->id)->update(['invoice_id' => null]);

            // Relink new items
            SalesItem::whereIn('id', $data['item_ids'])->update(['invoice_id' => $invoice->id]);

            return $invoice->fresh(['project', 'proposal.project', 'proposal.items', 'customer', 'pcmiBank.bank', 'items.product']);
        });
    }
    
    public function deleteInvoice($id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            throw new Exception("Invoice with ID {$id} not found");
        }

        $invoice->delete();
    }

    /**
     * Calculate Management Fee Value to Store (Logic for Proposal-based invoicing)
     * If percent: returns the Rate (e.g. 10).
     * If nominal: returns the Proportional Amount (e.g. 250000).
     */
    private function calculateProposalFeeValue(Proposal $proposal, $totalAmount)
    {
        $totalAmount = (float) $totalAmount;
        $managementFee = (float) ($proposal->management_fee ?? 0);

        if ($proposal->management_fee_type === 'percent') {
            // Return Rate
            return $managementFee;
        } else {
            // Nominal Proportional Logic
            // Return Calculated Amount
            $totalProposalAmount = (float) $proposal->items->sum('total_price');
            if ($totalProposalAmount > 0) {
                $proportion = $totalAmount / $totalProposalAmount;
                return $managementFee * $proportion;
            }
            return 0; // Or keep as 0 if inconsistent
        }
    }
}
