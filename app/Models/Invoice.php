<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'proposal_id',
        'customer_id',
        'billing_option_id',
        'pcmi_bank_id', 
        'code',
        'invoice_number', 
        'due_date',
        'sales_code', 
        'project_name', 
        'project_description', 
        'description',
        'billing_type', 
        'tax_type', 
        'total_amount',
        'total_received_amount',
        'balance_due',
        'total_pph23_deduction',
        'total_bank_charge',
        'status',
        'payment_status', 
        'management_fee_type',
        'management_fee',
        'vat_rate', 
    ];

    protected $appends = [
        'management_fee_amount',
        'sales_amount',
        'vat_amount',
        'invoice_amount',
    ];

    /**
     * Get the management fee calculation result.
     * Note: 'management_fee' column stores the RATE (if percent) or AMOUNT (if nominal).
     */
    public function getManagementFeeAmountAttribute()
    {
        $totalAmount = (float) $this->total_amount;
        $feeValue = (float) $this->management_fee;

        if ($this->management_fee_type === 'percent') {
            return ($totalAmount * $feeValue) / 100;
        }

        return $feeValue;
    }

    public function getSalesAmountAttribute()
    {
        return (float) $this->total_amount + $this->management_fee_amount;
    }

    public function getVatAmountAttribute()
    {
        if ($this->tax_type === 'No Tax') {
            return 0;
        }

        $salesAmount = $this->sales_amount;
        $vatRate = (float) $this->vat_rate;
        return ($salesAmount * $vatRate) / 100;
    }

    public function getInvoiceAmountAttribute()
    {
        return round($this->sales_amount + $this->vat_amount, 2);
    }

   public static function generateCode(Proposal $proposal): string
    {
        $date = now()->format('ymd'); // contoh: 251104
        $cleanCode = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($proposal->code)); // hilangkan simbol

        // Ambil 3 karakter pertama + 5 terakhir
        $prefixCode = substr($cleanCode, 0, 3) . substr($cleanCode, -5);

        // Updated Prefix to 'I' as per requirement
        $prefix = "I-{$prefixCode}-{$date}";

        // Hitung jumlah invoice untuk proposal ini hari ini
        $count = Invoice::where('proposal_id', $proposal->id)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        // 2 karakter random alfanumerik
        $random = strtoupper(Str::random(2));

        return sprintf("%s-%03d%s", $prefix, $count, $random);
    }

    public static function generateCodeFromProject(Project $project): string
    {
        $date = now()->format('ymd'); // Example: 250120
        
        // Project Code or ID
        $projCodeRaw = $project->code ?? (string)$project->id;
        // Clean and get last 5 chars
        $cleanCode = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($projCodeRaw));
        $projLast5 = substr($cleanCode, -5);
        $projLast5 = str_pad($projLast5, 5, '0', STR_PAD_LEFT);

        // Format: FIT-{ProjLast5}-{Date}-{Seq}
        // Requirement 1 says: "Prefix I (otomatis)..." for 'Invoice Code'.
        // But for consistency with previous discussion, if this is for FIT projects, maybe keep FIT prefix?
        // However, the prompt 1 says: "1 Invoice Code ... Prefix I ...". 
        // It doesn't distinguish between FIT and Regular for the code prefix itself in point 1.
        // I will use "I-FIT-" to distinguish or just "I-"?
        // Let's stick to "I-" standard to match requirement 1 strictly.
        // "I-000/Year of sales/..."
        // The user requirement specific format: "I-000/Year of sales/kalau bisa kode jenis activity nya"
        // This looks like a specific format request: "Prefix I (otomatis)-diikuti dengan Mandatory 3 angka/Year of sales yang didapat dari Sales code nya"
        
        // Since I don't have the full logic for "Activity Code" yet, I will keep the existing logic but change prefix to I- to align closer.
        $prefix = "I-FIT-{$projLast5}-{$date}";

        $count = Invoice::where('project_id', $project->id)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return sprintf("%s-%03d", $prefix, $count);
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pcmiBank(): BelongsTo
    {
        return $this->belongsTo(PcmiBank::class);
    }

    public function billingOption(): BelongsTo
    {
        return $this->belongsTo(BillingOption::class);
    }

    public function items(): HasMany   {
        return $this->hasMany(SalesItem::class, 'invoice_id');
    }

    public function receiveVouchers()
    {
        return $this->belongsToMany(ReceiveVoucher::class, 'invoice_receive_voucher')
            ->withPivot(['amount_applied', 'ppn_wapu_deduction', 'pph23_deduction', 'bank_charge', 'others_adjustment', 'adjustment_description'])
            ->withTimestamps();
    }

    public function recalculateReconciliation()
    {
        $vouchers = $this->receiveVouchers;
        
        $totalApplied = $vouchers->sum('pivot.amount_applied');
        $totalPph23 = $vouchers->sum('pivot.pph23_deduction');
        $totalBankCharge = $vouchers->sum('pivot.bank_charge');
        $totalWapu = $vouchers->sum('pivot.ppn_wapu_deduction');
        $totalOthers = $vouchers->sum('pivot.others_adjustment');

        $this->total_received_amount = (float) $totalApplied;
        $this->total_pph23_deduction = (float) $totalPph23;
        $this->total_bank_charge = (float) $totalBankCharge;

        // Balance Due = total_amount - (Total Applied + Deductions)
        // Deductions here include PPh23, Bank Charge, WAPU (if any), and Others
        $totalReductions = $totalApplied + $totalPph23 + $totalBankCharge + $totalWapu + $totalOthers;
        
        $this->balance_due = max(0, $this->total_amount - $totalReductions);

        // Auto-update payment status
        if ($this->balance_due <= 0 && $this->total_amount > 0) {
            $this->payment_status = 'FULLY PAID';
        } elseif ($this->total_received_amount > 0) {
            $this->payment_status = 'PARTLY PAID';
        } else {
            $this->payment_status = 'UNPAID';
        }

        $this->save();
    }

    /**
     * Shortcut to get Project via Proposal
     */


    /**
     * Calculate total from BOQs (optional helper)
     */
    public function calculateTotalAmount()
    {
        return $this->total_amount_items;
    }
}
