<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{


    public function all(Request $request)
    {
        $id = $request->input('id'); // powerhuman.com/api.company?id=1
        $name = $request->input('name'); // powerhuman.com/api.company?name=1
        $limit = $request->input('limit', 10); //setiap kit ngambil data berpa maksimal ngambil data
        // biasa digunakan untuk return data lebih dari 1



        //jika data satuan
        if ($id) {
            $company = Company::with(['users'])->find($id);

            //jika company didapatkan
            if ($company) {
                return ResponseFormatter::success($company);
            }

            return ResponseFormatter::error('Company not found');
        }

        $companies = Company::with(['users']); //pangil company user
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
}
