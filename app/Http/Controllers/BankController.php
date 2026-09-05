<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\BankRequest;
use App\Http\Services\BankService;
use App\Models\Bank;

class BankController extends Controller
{
    
    protected $bankService;

    public function __construct(BankService $bankService)
    {
        $this->bankService = $bankService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $searchValue = $request->search;
            $search = strtolower(trim(is_array($searchValue) ? ($searchValue['value'] ?? '') : ($searchValue ?? '')));
            $banks = Bank::query();

            return DataTables::eloquent($banks)
                ->filter(function ($query) use ($search) {
                    if ($search !== '') {
                        $query->where(function ($q) use ($search) {
                            $q->whereRaw('LOWER(bank_code) LIKE ?', ["%{$search}%"])
                              ->orWhereRaw('LOWER(bank_name) LIKE ?', ["%{$search}%"])
                              ->orWhereRaw('LOWER(bank_address) LIKE ?', ["%{$search}%"]);
                        });
                    }
                })
                ->addColumn('actions', function ($s) {
                    return '
                        <div class="dropdown table-action">
                            <a href="#" class="action-icon" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a  
                                    class="dropdown-item" 
                                    href="'.route('banks.read', ['bank_id' => $s->id]).'">
                                    <i class="ti ti-eye text-info"></i> View Detail
                                </a>
                                <a  
                                    class="dropdown-item c_bank_edit_btn" 
                                    href="#" 
                                    data-url="'.route('banks.read', ['bank_id' => $s->id]).'">
                                    <i class="ti ti-edit text-blue"></i> Edit
                                </a>
                                <a  
                                    class="dropdown-item c_bank_delete_btn" 
                                    href="javascript:void(0);" 
                                    data-url="'.route('banks.delete', ['bank_id' => $s->id]).'">
                                    <i class="ti ti-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('banks');
    }

    public function create(BankRequest $request): JsonResponse
    {
        try {
            $bank = $this->bankService->createBank($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Bank created successfully',
                'data' => $bank
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bank'
            ], 500);
        }
    }

    public function readAll(Request $request): JsonResponse
    {
        if($request->wantsJson() || $request->ajax()) {
            try {
                $banks = $this->bankService->getAllBanks();
                return response()->json([
                    'success' => true,
                    'data' => $banks
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Failed to fetch banks'
                ], 500);
            }
        }
        abort(404);    
    }

    public function read(Request $request, $bank_id)
    {
        if ($request->wantsJson() || $request->ajax()) {
            try {
                $bank = $this->bankService->getBankById($bank_id);
                return response()->json([
                    'success' => true,
                    'data' => $bank
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load bank'
                ], 500);
            }
        }

        $bank = $this->bankService->getBankById($bank_id);
        return view('banks.detail', compact('bank'));
    }

    public function update(BankRequest $request, $bank_id): JsonResponse
    {
        try {
            $bank = $this->bankService->updateBank($bank_id, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Bank updated successfully',
                'data' => $bank
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bank'
            ], 500);
        }
    }

    public function delete($bank_id): JsonResponse
    {
        try {
            $this->bankService->deleteBank($bank_id);
            return response()->json([
                'success' => true,
                'message' => 'Bank deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete bank'
            ], 500);
        }
    }
}
