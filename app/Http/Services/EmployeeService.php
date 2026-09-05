<?php

namespace App\Http\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Exception;

class EmployeeService
{
    public function createEmployee(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['code'] = Employee::generateCode();
            
            // Password hashing if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }
            
            $employee = Employee::create($data);
            return $employee->fresh(['bank']);
        });
    }

    public function getAllEmployees()
    {
        return Employee::with('bank')->get();
    }

    public function getEmployeeById($id)
    {
        $employee = Employee::with('bank')->find($id);
        if (!$employee) {
            throw new Exception("Employee with ID {$id} not found");
        }
        return $employee;
    }

    public function updateEmployee($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $employee = Employee::find($id);
            if (!$employee) {
                throw new Exception("Employee with ID {$id} not found");
            }

            // Password hashing if changed
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            } else {
                unset($data['password']); // Don't overwrite if empty
            }

            $employee->update($data);
            return $employee->fresh(['bank']);
        });
    }

    public function deleteEmployee($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            throw new Exception("Employee with ID {$id} not found");
        }
        $employee->delete();
    }
}
