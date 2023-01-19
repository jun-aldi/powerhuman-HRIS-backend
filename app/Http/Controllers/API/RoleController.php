<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id'); // powerhuman.com/api.company?id=1
        $name = $request->input('name'); // powerhuman.com/api.company?name=1
        $limit = $request->input('limit', 10); //setiap kit ngambil data berpa maksimal ngambil data
        // biasa digunakan untuk return data lebih dari 1

        $roleQuery = Role::where('company_id', $request->company_id);

        //jika data satuan
        if ($id) {
            $role = $roleQuery->find($id);

            //jika role didapatkan
            if ($role) {
                return ResponseFormatter::success($role, 'Role Found');
            }

            return ResponseFormatter::error('Role not found', 404);
        }

        //pangil role yg dikelola user
        $roles = $roleQuery;
        //ingin manggil siapa yang megang role


        //jika ingin cari berdasarkan nama
        //powerhuman.com/api/role?name=kunto
        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Roles found'
        );
    }

    public function create(CreateRoleRequest $request)
    {

        try {
            // Upload icon


            // Create Role
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,

            ]);

            if (!$role) {
                throw new Exception('Role not created');
            }

            return ResponseFormatter::success($role, 'Role Created');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        try {

            // Search id
            $role = Role::find($id);

            // If id not found
            if (!$role) {
                // throw new Exception('Role not found');
            }

            // Update Role
            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            // Return Response
            return ResponseFormatter::success($role, 'Role Updated');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Get role
            $role = Role::find($id);

            // TODO: Check if role is owned by user

            // Check if role exists
            if (!$role) {
                throw new Exception('Role not found');
            }

            // Delete role
            $role->delete();

            return ResponseFormatter::success('Role deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
