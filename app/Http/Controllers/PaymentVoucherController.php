<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\PaymentVoucherRequest;
use App\Http\Services\PaymentVoucherService;
use App\Models\PaymentVoucher;
use Yajra\DataTables\Facades\DataTables;

class PaymentVoucherController extends Controller
{
    protected $pvService;

    public function __construct(PaymentVoucherService $pvService)
    {
        $this->pvService = $pvService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $pvs = PaymentVoucher::with(['pcmiBank.bank']);

            return DataTables::eloquent($pvs)
                ->addColumn('bank_name', fn($pv) => $pv->pcmiBank?->bank?->name ?: '-')
                ->addColumn('amount_formatted', fn($pv) => formatRupiah($pv->amount))
                ->addColumn('actions', function ($pv) {
                    return '
                        <div class="dropdown table-action">
                            <a href="javascript:void(0)" class="action-icon" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="' . route('pvs.read', ['pv_id' => $pv->id]) . '">
                                    <i class="ti ti-eye text-info"></i> Details
                                </a>
                                <a class="dropdown-item c_pv_edit_btn" href="javascript:void(0)" data-url="' . route('pvs.read', ['pv_id' => $pv->id]) . '">
                                    <i class="ti ti-edit text-blue"></i> Edit
                                </a>
                                <a class="dropdown-item c_pv_delete_btn" href="javascript:void(0)" data-url="' . route('pvs.read', ['pv_id' => $pv->id]) . '">
                                    <i class="ti ti-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('payment-vouchers');
    }

    public function create(PaymentVoucherRequest $request): JsonResponse
    {
        try {
            $pv = $this->pvService->create($request->validated());
            return response()->json(['success' => true, 'message' => 'Payment Voucher created successfully', 'data' => $pv], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function read(Request $request, $pv_id)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'data' => $this->pvService->getById($pv_id)], 200);
        }
        $pv = $this->pvService->getById($pv_id);
        return view('payment-vouchers.detail', compact('pv'));
    }

    public function update(PaymentVoucherRequest $request, $pv_id): JsonResponse
    {
        try {
            $pv = $this->pvService->update($pv_id, $request->validated());
            return response()->json(['success' => true, 'message' => 'Payment Voucher updated successfully', 'data' => $pv], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($pv_id): JsonResponse
    {
        try {
            $this->pvService->delete($pv_id);
            return response()->json(['success' => true, 'message' => 'Payment Voucher deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
