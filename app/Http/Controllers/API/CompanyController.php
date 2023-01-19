<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\User;

class CompanyController extends Controller
{


    public function fetch(Request $request)
    {
        $id = $request->input('id'); // powerhuman.com/api.company?id=1
        $name = $request->input('name'); // powerhuman.com/api.company?name=1
        $limit = $request->input('limit', 10); //setiap kit ngambil data berpa maksimal ngambil data
        // biasa digunakan untuk return data lebih dari 1

        $companyQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });

        //jika data satuan
        if ($id) {
            $company = $companyQuery->find($id);

            //jika company didapatkan
            if ($company) {
                return ResponseFormatter::success($company, 'Company Found');
            }

            return ResponseFormatter::error('Company not found', 404);
        }
        //pangil company yg dikelola user
        $companies = $companyQuery;
        //ingin manggil siapa yang megang company


        //jika ingin cari berdasarkan nama
        //powerhuman.com/api/company?name=kunto
        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies found'
        );
    }

    public function create(CreateCompanyRequest $request)
    {

        try {
            // Upload Logo
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            // Create Company
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path,
            ]);

            if (!$company) {
                throw new Exception('Company not created');
            }

            //Attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            // Load users at company
            $company->load('users');

            return ResponseFormatter::success($company, 'Company Created');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {

            // Search id
            $company = Company::find($id);

            // If id not found
            if (!$company) {
                throw new Exception('Company not found');
            }

            // Upload logo
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            // Update logo
            $company->update([
                'name' => $request->name,
                'logo' => $path
            ]);

            // Return Response
            return ResponseFormatter::success($company, 'Company Updated');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }
}
