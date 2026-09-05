<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Invoice;
use App\Models\PdfTemplate;
use App\Http\Requests\InvoiceRequest;
use App\Http\Services\InvoiceService;
use App\Http\Services\PdfTemplateService;

class InvoiceController extends Controller
{
    protected $invoiceService;
    protected $templateService;

    public function __construct(InvoiceService $invoiceService, PdfTemplateService $templateService)
    {
        $this->invoiceService = $invoiceService;
        $this->templateService = $templateService;
    }
    
    /**
     * Retrieve paginated users with ordering, excluding the password field,
     * and pass the data to the manage-users view.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        
        if($request->ajax())
        {
            $searchValue = $request->search;
            $search = strtolower(trim(is_array($searchValue) ? ($searchValue['value'] ?? '') : ($searchValue ?? '')));
            $invoices = Invoice::with(['customer', 'proposal', 'items', 'project', 'pcmiBank.bank']);

            $result = DataTables::eloquent($invoices)
                ->filter(function ($query) use ($search) {
                    if ($search !== '') {
                        $query->where(function ($q) use ($search) {
                            $q->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(invoice_number) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(payment_method) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(bill_to) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(ship_to) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(note) LIKE ?', ["%{$search}%"])
                            ->orWhereHas('customer', fn($p) =>
                                $p->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            )
                            ->orWhereHas('proposal', fn($p) =>
                                $p->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                            )
                            ->orWhereHas('project', fn($p) =>
                                $p->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                                  ->orWhereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            );
                        });
                    }
                })
                ->addColumn('project_name', fn($inv) => $inv->project?->name ?: '-')
                ->addColumn('proposal_code', fn($inv) => $inv->proposal?->code ?: '-')
                ->addColumn('sales_code', function($inv) {
                    if ($inv->proposal && $inv->proposal->sales_code) {
                        return $inv->proposal->sales_code;
                    }
                    if ($inv->project && $inv->project->sales_code) {
                        return $inv->project->sales_code;
                    }
                    return '-';
                })
                ->addColumn('invoice_amount', fn($invoice) => formatRupiah($invoice->invoice_amount))
                ->addColumn('actions', function($invoice) {
                    return '
                        <div class="dropdown table-action">
                            <a href="javascript:void(0)" class="action-icon" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a  
                                    class="dropdown-item" 
                                    href="'.route('invoices.read', ['invoice_id' => $invoice->id]).'"
                                >
                                    <i class="ti ti-eye text-info"></i> View Detail
                                </a>
                                <a 
                                    class="dropdown-item" 
                                    href="'.route('invoices.pdf', ['invoice_id' => $invoice->id]).'" 
                                    target="_blank"
                                >
                                    <i class="ti ti-file-type-pdf text-danger"></i> Generate PDF
                                </a>
                                <a 
                                    class="dropdown-item c_invoice_edit_btn" 
                                    href="javascript:void(0)" 
                                    data-url="'.route('invoices.read', ['invoice_id' => $invoice->id]).'"
                                    data-proposal_id="'.$invoice->proposal?->id.'"
                                    data-project_id="'.$invoice->project?->id.'"
                                >
                                    <i class="ti ti-edit text-blue"></i> Edit
                                </a>
                                <a 
                                    class="dropdown-item c_invoice_delete_btn" 
                                    href="javascript:void(0)" 
                                    data-url="'.route('invoices.delete', ['invoice_id' => $invoice->id]).'"
                                >
                                    <i class="ti ti-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>
                    ';
                    }
                )
                ->rawColumns(['description', 'notes', 'status', 'ship_to', 'terms_and_conditions', 'actions'])
                ->make(true);
                
            return $result; 
        }

        return view('invoices');
    }

    
    public function create(InvoiceRequest $request): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->createInvoice($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => $invoice
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to create Invoice'
            ], 500);
        }
    }

    public function readAll(): JsonResponse
    {
        try {
            $invoices = $this->invoiceService->getAllInvoices();
            return response()->json([
                'status' => 'success',
                'data' => $invoices
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Internal Server Error'
            ], 500);
        }
    }

    public function readUnpaid(Request $request): JsonResponse
    {
        try {
            $customerId = $request->query('customer_id');
            $invoices = $this->invoiceService->getUnpaidInvoices($customerId);

            return response()->json([
                'success' => true,
                'data' => $invoices
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to fetch unpaid invoices'
            ], 500);
        }
    }

    public function read(Request $request, $invoice_id)
    {
        if($request->wantsJson() || $request->ajax()) {
            try {
                $invoice = $this->invoiceService->getInvoiceById($invoice_id);
                return response()->json([
                    'success' => true,
                    'data' => $invoice
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Internal Server Error'
                ], 500);
            }
        }
         
        $invoice = $this->invoiceService->getInvoiceById($invoice_id);
        return view('invoices.detail', compact('invoice'));
    }

    public function update(InvoiceRequest $request): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->updateInvoice($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'data' => $invoice
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Internal Server Error'
            ], 500);
        }
    }

    public function delete($invoice_id): JsonResponse
    {
        try {
            $this->invoiceService->deleteInvoice($invoice_id);
            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Internal Server Error'
            ], 500);
        }
    }

    public function generatePdf($invoice_id)
    {
        try {
            $invoice = Invoice::with(['project', 'customer', 'proposal', 'items'])->findOrFail($invoice_id);
            
            $isRegular = $invoice->project == null && $invoice->proposal != null;
            $isFit = $invoice->project != null && $invoice->proposal == null;

            if (!$isRegular && !$isFit) {
                 return response()->json(['success' => false, 'message' => 'Invalid Invoice Type'], 400);
            }

            // Get Invoice Template
            $template = $this->getInvoiceTemplate();
            if (!$template) {
                return response()->json(['success' => false, 'message' => 'No active invoice template found'], 404);
            }

            // Prepare Data
            $itemsData = $this->getInvoiceItemsData($invoice, $isRegular);
            $totalsHtml = $this->getInvoiceTotalsHtml($invoice, $isRegular);
            $logoData = $this->getLogoData();

            // Map data
            $data = [
                'invoice_code' => $invoice->code,
                'invoice_date' => \Carbon\Carbon::parse($invoice->invoice_date)->format('d F Y'),
                'customer_name' => $invoice->customer->name ?? '-',
                'bill_to' => $invoice->bill_to,
                'due_date' => \Carbon\Carbon::parse($invoice->due_date)->format('d F Y'),
                'payment_method' => $invoice->payment_method,
                'totals_rows' => $totalsHtml, 
                'notes' => $invoice->note,
                'invoice_table_header' => $itemsData['header'],
                'invoice_items' => $itemsData['items'],
                'logo_path' => $logoData,
            ];

            // Render
            $html = $this->templateService->renderTemplate($template->id, $data);

            // Inject Script
            $filename = 'Invoice-' . $invoice->code . '.pdf';
            $script = $this->getHtml2PdfScript($filename);
            
            $html = str_replace('</body>', $script . '</body>', $html);

            return response($html);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to generate PDF'
            ], 500);
        }
    }

    private function getInvoiceTemplate()
    {
        $template = PdfTemplate::where('type', 'invoice')
            ->where('is_active', true)
            ->where('name', 'Default Invoice')
            ->first();

        if (!$template) {
            $template = PdfTemplate::where('type', 'invoice')
                ->where('is_active', true)
                ->first();
        }
        return $template;
    }

    private function getInvoiceItemsData($invoice, $isRegular)
    {
        $headerHtml = '';
        $itemsHtml = '';

        if ($isRegular) {
            $pricingModel = $invoice->proposal->pricing_model; 

            $headerHtml .= '<th class="pdf-text-center" style="width: 30px;">NO</th>';
            $headerHtml .= '<th>DESCRIPTION</th>';
            $headerHtml .= '<th class="pdf-text-center" style="width: 60px;">QTY</th>';
            $headerHtml .= '<th class="pdf-text-center" style="width: 60px;">FREQ</th>';
            $headerHtml .= '<th class="pdf-text-right" style="width: 100px; white-space: nowrap;">UNIT PRICE</th>';
            $headerHtml .= '<th class="pdf-text-right" style="width: 100px; white-space: nowrap;">TOTAL</th>';

            if ($pricingModel === 'XXX') {
                $description = $invoice->proposal->pricing_model_description ?? 'Project Implementation';
                $totalAmountItems = $invoice->items->sum('total_price');

                $itemsHtml .= '<tr>';
                $itemsHtml .= '<td class="pdf-text-center">1</td>';
                $itemsHtml .= '<td>' . $description . '</td>';
                $itemsHtml .= '<td class="nowrap">-</td>'; 
                $itemsHtml .= '<td class="nowrap">-</td>'; 
                $itemsHtml .= '<td class="pdf-text-right nowrap">' . formatRupiah($totalAmountItems) . '</td>';
                $itemsHtml .= '<td class="pdf-text-right nowrap">' . formatRupiah($totalAmountItems) . '</td>';
                $itemsHtml .= '</tr>';

            } else {
                $groupedByHeader = $invoice->items->groupBy('header');
                $sortedHeaders = $groupedByHeader->sortBy(function ($items, $key) {
                    return empty($key) ? 0 : 1;
                }, SORT_NUMERIC);

                $headerIndex = 0;
                foreach ($sortedHeaders as $header => $itemsWithSameHeader) {
                    if (!empty($header)) {
                        $headerLabel = chr(65 + $headerIndex); 
                        $headerTotal = $itemsWithSameHeader->sum('total_price');

                        $itemsHtml .= '<tr class="boq-header">';
                        $itemsHtml .= '<td class="pdf-text-center">' . $headerLabel . '</td>';
                        $itemsHtml .= '<td colspan="4">' . strtoupper($header) . '</td>';
                        $itemsHtml .= '<td class="pdf-text-right">' . formatRupiah($headerTotal) . '</td>';
                        $itemsHtml .= '</tr>';

                        $groupedBySubheader = $itemsWithSameHeader->groupBy('subheader');
                        $sortedSubheaders = $groupedBySubheader->sortBy(function ($items, $key) {
                            return empty($key) ? 0 : 1; 
                        }, SORT_NUMERIC);
                        
                        $subheaderIndex = 1;
                        foreach ($sortedSubheaders as $subheader => $itemsWithSameSubheader) {
                            if (!empty($subheader)) {
                                $itemsHtml .= '<tr class="boq-subheader">';
                                $itemsHtml .= '<td class="pdf-text-center">'. $headerLabel . '.' . $subheaderIndex .'</td>';
                                $itemsHtml .= '<td colspan="5">' . $subheader . '</td>';
                                $itemsHtml .= '</tr>';
                                $subheaderIndex++;
                            }

                            $itemIndex = 1; 
                            foreach ($itemsWithSameSubheader as $item) {
                                $itemsHtml .= $this->renderItemRow($item, $itemIndex);
                                $itemIndex++;
                            }
                        }
                        $headerIndex++;
                    } else {
                        $itemIndex = 1;
                        foreach ($itemsWithSameHeader as $item) {
                            $itemsHtml .= $this->renderItemRow($item, $itemIndex);
                            $itemIndex++;
                        }
                    }
                }

                if ($groupedByHeader->isEmpty()) {
                    $itemsHtml .= '<tr><td colspan="6" class="pdf-text-center">No Items Found</td></tr>';
                }
            }
        } else {
            // Is Fit
            $headerHtml .= '<th class="pdf-text-center" style="width: 30px;">NO</th>';
            $headerHtml .= '<th>DESCRIPTION</th>';
            $headerHtml .= '<th class="pdf-text-right" style="width: 100px; white-space: nowrap;">UNIT PRICE</th>';
            $headerHtml .= '<th class="pdf-text-right" style="width: 100px; white-space: nowrap;">TOTAL</th>';

            $itemsHtml .= '<tr>';
            $itemsHtml .= '<td class="pdf-text-center">1</td>';
            $itemsHtml .= '<td>' . $invoice->description . '</td>';
            $itemsHtml .= '<td class="pdf-text-right nowrap">' . formatRupiah($invoice->total_amount) . '</td>';
            $itemsHtml .= '<td class="pdf-text-right nowrap">' . formatRupiah($invoice->total_amount) . '</td>';
            $itemsHtml .= '</tr>';
        }

        return ['header' => $headerHtml, 'items' => $itemsHtml];
    }

    private function renderItemRow($item, $index)
    {
        return '<tr>' .
            '<td class="pdf-text-center">' . $index . '</td>' .
            '<td>' . ($item->description ?: '-') . '</td>' .
            '<td class="nowrap">' . $item->title1_value . ' ' . $item->title1_key . '</td>' .
            '<td class="nowrap">' . $item->title2_value . ' ' . $item->title2_key . '</td>' .
            '<td class="pdf-text-right nowrap">' . formatRupiah($item->selling_price) . '</td>' .
            '<td class="pdf-text-right nowrap">' . formatRupiah($item->total_price) . '</td>' .
            '</tr>';
    }

    private function getInvoiceTotalsHtml($invoice, $isRegular)
    {
        $colspan = $isRegular ? 5 : 3;
        
        // Common Values
        
        if ($isRegular) {
             $basicSum = $invoice->items->sum('total_price');
             
             $feeType = $invoice->proposal->management_fee_type;
             $feeRate = $invoice->proposal->management_fee;
             $vatRate = $invoice->proposal->vat_rate;

             if ($feeType != 'nominal') { // Default to percent if not nominal
                 $feeAmount = round($basicSum * ($feeRate / 100), 2);
             } else {
                 // Proportional Fee Calculation for Nominal
                 $proposalTotal = $invoice->proposal->total_amount_items ?: 1; 
                 $feeAmount = round(($basicSum / $proposalTotal) * $feeRate, 2);
             }
             
             // Recalculate dependent values based on new Fee
             $salesAmount = $basicSum + $feeAmount;
             $vatAmount = round($salesAmount * ($vatRate / 100), 2);
             $grandTotal = $salesAmount + $vatAmount;

        } else {
             $basicSum = $invoice->total_amount;
             $feeAmount = $invoice->management_fee_amount;
             $feeType = $invoice->management_fee_type;
             $feeRate = $invoice->management_fee;
             $vatRate = $invoice->vat_rate;

             $salesAmount = $invoice->sales_amount;
             $vatAmount = $invoice->vat_amount;
             $grandTotal = $invoice->invoice_amount;
        }

        $html = '';

        // 1. Basic Price Sum
        $html .= '<tr>';
        $html .= '<td colspan="'.$colspan.'" class="pdf-totals-label pdf-text-right pr-80">Basic Price Sum</td>';
        $html .= '<td class="pdf-totals-value">' . formatRupiah($basicSum) . '</td>';
        $html .= '</tr>';

        // 2. Management Fee
        $feeLabel = "Management Fee";
        if ($feeType === 'percent') {
            $feeLabel .= " (" . formatRupiah($feeRate) . "%)";
        }
        $html .= '<tr>';
        $html .= '<td colspan="'.$colspan.'" class="pdf-totals-label pdf-text-right pr-80">' . $feeLabel . '</td>';
        $html .= '<td class="pdf-totals-value">' . formatRupiah($feeAmount) . '</td>';
        $html .= '</tr>';

        // 3. Sales Amount
        $html .= '<tr>';
        $html .= '<td colspan="'.$colspan.'" class="pdf-totals-label pdf-text-right pr-80">Sales Amount</td>';
        $html .= '<td class="pdf-totals-value">' . formatRupiah($salesAmount) . '</td>';
        $html .= '</tr>';

        // 4. VAT
        $html .= '<tr>';
        $html .= '<td colspan="'.$colspan.'" class="pdf-totals-label pdf-text-right pr-80">VAT (' . formatRupiah($vatRate) . '%)</td>';
        $html .= '<td class="pdf-totals-value">' . formatRupiah($vatAmount) . '</td>';
        $html .= '</tr>';

        // 5. Grand Total
        $html .= '<tr>';
        $html .= '<td colspan="'.$colspan.'" class="pdf-totals-label pdf-text-right pdf-grand-total pr-80">Total Amount</td>';
        $html .= '<td class="pdf-totals-value pdf-grand-total">' . formatRupiah($grandTotal) . '</td>';
        $html .= '</tr>';

        return $html;
    }

    private function getLogoData()
    {
        $logoPath = public_path('build/img/your-logo.png');
        if (file_exists($logoPath)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
        return '';
    }

    private function getHtml2PdfScript($filename)
    {
        return '
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
            <style>
                #pdf-loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: white;
                    z-index: 9999;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    flex-direction: column;
                    font-family: sans-serif;
                }
                .spinner {
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #4059C6;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    animation: spin 1s linear infinite;
                    margin-bottom: 15px;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
            <div id="pdf-loading-overlay">
                <div class="spinner"></div>
                <div>Generating PDF...</div>
            </div>
            <script>
                window.onload = function () {
                    const pageElement = document.querySelector(".pdf-page-wrapper");
                    const headerElement = document.querySelector(".pdf-header");
                    const overlay = document.getElementById("pdf-loading-overlay");

                    html2pdf()
                        .from(headerElement)
                        .set({
                            html2canvas: { scale: 2, useCORS: true }
                        })
                        .toCanvas()
                        .get("canvas")
                        .then(function (canvas) {

                            const imgData = canvas.toDataURL("image/png");
                            const imgWidth = 210; 
                            const imgHeight = (canvas.height * imgWidth) / canvas.width;
                            
                            const opt = {
                                display: "flex",
                                flexDirection: "column",
                                margin: [imgHeight, 0, 20, 0],
                                filename: "' . $filename . '",
                                image: { type: "jpeg", quality: 0.98 },
                                html2canvas: { scale: 2, useCORS: true, scrollY: 0, letterRendering: true },
                                jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
                                pagebreak: { mode: ["css", "legacy"] }
                            };

                            pageElement.style.fontFamily = "Arial, sans-serif";

                            html2pdf()
                                .set(opt)
                                .from(pageElement)
                                .toPdf()
                                .get("pdf")
                                .then(function (pdf) {
                                    const totalPages = pdf.internal.getNumberOfPages();
                                    for (let i = 1; i <= totalPages; i++) {
                                        pdf.setPage(i);
                                        pdf.addImage(imgData, "PNG", 0, 0, imgWidth, imgHeight);
                                        pdf.setFont("helvetica", "normal");
                                        pdf.setFontSize(8);
                                        pdf.setTextColor(150);
                                        pdf.text(
                                            "Page " + i + " of " + totalPages,
                                            105,
                                            290,
                                            null,
                                            null,
                                            "center"
                                        );
                                    }
                                })
                                .save()
                                .then(function() {
                                    headerElement.style.padding = "padding: 0 15mm 0 15mm";
                                    pageElement.style.padding = "0 15mm 20mm 15mm";
                                    overlay.style.display = "none";
                                });
                        });
                };
            </script>
        ';
    }
}
