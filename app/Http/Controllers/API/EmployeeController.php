<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Employee;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id'); // powerhuman.com/api.company?id=1
        $name = $request->input('name');
        $email = $request->input('email');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $team_id = $request->input('team_id');
        $role_id = $request->input('role_id');
        $company_id = $request->input('company_id');
        $limit = $request->input('limit', 10); //setiap kit ngambil data berpa maksimal ngambil data
        // biasa digunakan untuk return data lebih dari 1

        $employeeQuery = Employee::with('team', 'role');

        //jika data satuan
        if ($id) {
            $employee = $employeeQuery->with('team', 'role')->find($id);

            //jika employee didapatkan
            if ($employee) {
                return ResponseFormatter::success($employee, 'Employee Found');
            }

            return ResponseFormatter::error('Employee not found', 404);
        }

        //pangil employee yg dikelola user
        $employees = $employeeQuery;
        //ingin manggil siapa yang megang employee


        //jika ingin cari berdasarkan nama
        //powerhuman.com/api/employee?name=kunto
        if ($name) {
            $employees->where('name', 'like', '%' . $name . '%');
        }

        if ($email) {
            $employees->where('email', $email);
        }

        if ($age) {
            $employees->where('age', $age);
        }

        if ($phone) {
            $employees->where('phone', 'like', '%' . $phone . '%');
        }

        if ($role_id) {
            $employees->where('role_id', $role_id);
        }
        if ($team_id) {
            $employees->where('team_id', $team_id);
        }
        if ($company_id) {
            //ambil relasi dari company mana
            //pakai where has
            $employees->whereHas('team', function ($query) use ($company_id) {
                $query->where('company_id', $company_id);
            });
            //kalau employee punya team kita akan
        }

        return ResponseFormatter::success(
            $employees->paginate($limit),
            'Employees found'
        );
    }

    public function create(CreateEmployeeRequest $request)
    {

        try {
            // Upload icon
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            // Create Employee
            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => isset($path) ? $path : '',
                'team_id' => $request->team_id,
                'role_id' => $request->role_id,
            ]);

            if (!$employee) {
                throw new Exception('Employee not created');
            }

            return ResponseFormatter::success($employee, 'Employee Created');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        try {

            // Search id
            $employee = Employee::find($id);

            // If id not found
            if (!$employee) {
                // throw new Exception('Employee not found');
            }

            // Upload logo
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            // Update Employee
            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => isset($path) ? $path : $employee->photo, // ada path gak
                // kalau ada path pakai baru
                //kalau gk ada pakai data yang baru
                'team_id' => $request->team_id,
                'role_id' => $request->role_id,
            ]);

            // Return Response
            return ResponseFormatter::success($employee, 'Employee Updated');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Get employee
            $employee = Employee::find($id);

            // TODO: Check if employee is owned by user

            // Check if employee exists
            if (!$employee) {
                throw new Exception('Employee not found');
            }

            // Delete employee
            $employee->delete();

            return ResponseFormatter::success('Employee deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
