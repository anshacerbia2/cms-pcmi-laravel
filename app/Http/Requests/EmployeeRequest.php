<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class EmployeeRequest extends ApiFormRequest
{
    public function validationData()
    {
        return array_merge($this->all(), [
            'id' => $this->route('employee_id'),
        ]);
    }

    public function rules()
    {
        $action = $this->route()->getName();

        switch ($action) {
            case 'employees.create':
                return $this->createRules();
            case 'employees.update':
                return $this->updateRules();
            default:
                return [];
        }
    }

    protected function createRules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'ktp_number' => ['nullable', 'string', 'size:16', 'unique:employees,ktp_number'],
            'sim_number' => ['nullable', 'string', 'max:50'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'npwp_number' => ['nullable', 'string', 'max:50'],
            'bpjs_number' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', Rule::in(['TK', 'K1', 'K2', 'K3'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            
            // Address fields
            'ktp_address' => ['nullable', 'string'],
            'ktp_province' => ['nullable', 'string', 'max:100'],
            'ktp_city' => ['nullable', 'string', 'max:100'],
            'ktp_district' => ['nullable', 'string', 'max:100'],
            'ktp_village' => ['nullable', 'string', 'max:100'],
            
            'current_address' => ['nullable', 'string'],
            'current_province' => ['nullable', 'string', 'max:100'],
            'current_city' => ['nullable', 'string', 'max:100'],
            'current_district' => ['nullable', 'string', 'max:100'],
            'current_village' => ['nullable', 'string', 'max:100'],
            
            'join_date' => ['nullable', 'date'],
            'position' => ['nullable', 'string', 'max:100'],
            
            'bank_id' => ['nullable', 'exists:banks,id'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            
            'username' => ['nullable', 'string', 'min:3', 'max:50', 'unique:employees,username'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function updateRules()
    {
        $id = $this->route('employee_id');
        return array_merge($this->createRules(), [
            'id' => ['required', 'exists:employees,id'],
            'ktp_number' => ['nullable', 'string', 'size:16', Rule::unique('employees', 'ktp_number')->ignore($id)],
            'username' => ['nullable', 'string', 'min:3', 'max:50', Rule::unique('employees', 'username')->ignore($id)],
        ]);
    }

    public function authorize()
    {
        return true;
    }
}
