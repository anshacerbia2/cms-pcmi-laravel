<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\EmployeeRequest;
use App\Http\Services\EmployeeService;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $searchValue = $request->search;
            $search = strtolower(trim(is_array($searchValue) ? ($searchValue['value'] ?? '') : ($searchValue ?? '')));
            
            $employees = Employee::query();

            return DataTables::eloquent($employees)
                ->filter(function ($query) use ($search) {
                    if ($search !== '') {
                        $query->where(function ($q) use ($search) {
                            $q->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(position) LIKE ?', ["%{$search}%"]);
                        });
                    }
                })
                ->addColumn('actions', function ($e) {
                    return '
                        <div class="dropdown table-action">
                            <a href="#" class="action-icon" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a  
                                    class="dropdown-item" 
                                    href="'.route('employees.read', ['employee_id' => $e->id]).'"
                                >
                                    <i class="ti ti-eye text-info"></i> View Detail
                                </a>
                                <a  
                                    class="dropdown-item c_employee_edit_btn" 
                                    href="#" 
                                    data-id="'.$e->id.'" 
                                    data-url="'.route('employees.read', ['employee_id' => $e->id]).'"
                                >
                                    <i class="ti ti-edit text-blue"></i> Edit
                                </a>
                                <a  
                                    class="dropdown-item c_employee_delete_btn" 
                                    href="javascript:void(0);" 
                                    data-id="'.$e->id.'" 
                                    data-url="'.route('employees.delete', ['employee_id' => $e->id]).'"
                                >
                                    <i class="ti ti-trash text-danger"></i> Delete
                                </a>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('employees');
    }

    public function create(EmployeeRequest $request): JsonResponse
    {
        try {
            $employee = $this->employeeService->createEmployee($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => $employee
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to create Employee'
            ], 500);
        }
    }

    public function readAll(Request $request): JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            try {
                $employees = $this->employeeService->getAllEmployees();
                return response()->json([
                    'status' => 'success',
                    'data' => $employees
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Failed to load Employees'
                ], 500);
            }
        }
        abort(404);
    }

    public function read(Request $request, $employee_id)
    {
        if ($request->wantsJson() || $request->ajax()) {
            try {
                $employee = $this->employeeService->getEmployeeById($employee_id);
                return response()->json([
                    'success' => true,
                    'data' => $employee
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Failed to load Employee'
                ], 500);
            }
        }

        try {
            $employee = $this->employeeService->getEmployeeById($employee_id);
            return view('employees.detail', compact('employee'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(EmployeeRequest $request, $employee_id): JsonResponse
    {
        try {
            $employee = $this->employeeService->updateEmployee($employee_id, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully',
                'data' => $employee
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to update Employee'
            ], 500);
        }
    }

    public function delete($employee_id): JsonResponse
    {
        try {
            $this->employeeService->deleteEmployee($employee_id);
            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to delete Employee'
            ], 500);
        }
    }

    public function generateCodes()
    {
        try {
            $code = Employee::generateCode();
            return response()->json([
                'success' => true,
                'data' => ['code' => $code]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating employee code: ' . $e->getMessage()
            ], 500);
        }
    }
}
