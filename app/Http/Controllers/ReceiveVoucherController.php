<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ReceiveVoucherRequest;
use App\Http\Services\ReceiveVoucherService;
use App\Models\ReceiveVoucher;
use Yajra\DataTables\Facades\DataTables;

class ReceiveVoucherController extends Controller
{
    protected $rvService;

    public function __construct(ReceiveVoucherService $rvService)
    {
        $this->rvService = $rvService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $rvs = ReceiveVoucher::with(['pcmiBank.bank']);

            return DataTables::eloquent($rvs)
                ->addColumn('bank_name', fn($rv) => $rv->pcmiBank?->bank?->bank_name ?: '-')
                ->addColumn('payer_display', function($rv) {
                    if ($rv->payer_type === 'Others') return $rv->payer_name_manual;
                    if ($rv->payer_type === 'Unknown') return 'Unknown';
                    
                    // For linked payers, we'd ideally load the model, but to keep it simple for now:
                    if ($rv->payer_type === 'Customer') return \App\Models\Customer::find($rv->payer_id)?->name ?: '-';
                    if ($rv->payer_type === 'Supplier') return \App\Models\Supplier::find($rv->payer_id)?->name ?: '-';
                    if ($rv->payer_type === 'Employee') return \App\Models\User::find($rv->payer_id)?->name ?: '-';
                    
                    return '-';
                })
                ->addColumn('amount_formatted', function($rv) {
                    $currency = $rv->currency === 'Others' ? $rv->currency_manual : $rv->currency;
                    return ($currency ?: 'IDR') . ' ' . number_format($rv->amount, 2, ',', '.');
                })
                ->addColumn('actions', function ($rv) {
                    return '
                        <div class="dropdown table-action">
                            <a href="javascript:void(0)" class="action-icon" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="' . route('rvs.read', ['rv_id' => $rv->id]) . '">
                                    <i class="ti ti-eye text-info"></i> Details
                                </a>
                                <a class="dropdown-item c_rv_edit_btn" href="javascript:void(0)" data-url="' . route('rvs.read', ['rv_id' => $rv->id]) . '">
                                    <i class="ti ti-edit text-blue"></i> Edit
                                </a>
                                <a class="dropdown-item c_rv_delete_btn" href="javascript:void(0)" data-url="' . route('rvs.delete', ['rv_id' => $rv->id]) . '">
                                    <i class="ti ti-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('receive-vouchers');
    }

    public function create(ReceiveVoucherRequest $request): JsonResponse
    {
        try {
            $rv = $this->rvService->create($request->validated());
            return response()->json(['success' => true, 'message' => 'Receive Voucher created successfully', 'data' => $rv], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function read(Request $request, $rv_id)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'data' => $this->rvService->getById($rv_id)], 200);
        }
        $rv = $this->rvService->getById($rv_id);
        return view('receive-vouchers.detail', compact('rv'));
    }

    public function update(ReceiveVoucherRequest $request, $rv_id): JsonResponse
    {
        try {
            $rv = $this->rvService->update($rv_id, $request->validated());
            return response()->json(['success' => true, 'message' => 'Receive Voucher updated successfully', 'data' => $rv], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($rv_id): JsonResponse
    {
        try {
            $this->rvService->delete($rv_id);
            return response()->json(['success' => true, 'message' => 'Receive Voucher deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
