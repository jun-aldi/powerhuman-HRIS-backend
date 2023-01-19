<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Team;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id'); // powerhuman.com/api.company?id=1
        $name = $request->input('name'); // powerhuman.com/api.company?name=1
        $limit = $request->input('limit', 10); //setiap kit ngambil data berpa maksimal ngambil data
        // biasa digunakan untuk return data lebih dari 1

        $teamQuery = Team::where('company_id', $request->company_id);

        //jika data satuan
        if ($id) {
            $team = $teamQuery->find($id);

            //jika team didapatkan
            if ($team) {
                return ResponseFormatter::success($team, 'Team Found');
            }

            return ResponseFormatter::error('Team not found', 404);
        }

        //pangil team yg dikelola user
        $teams = $teamQuery;
        //ingin manggil siapa yang megang team


        //jika ingin cari berdasarkan nama
        //powerhuman.com/api/team?name=kunto
        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Teams found'
        );
    }

    public function create(CreateTeamRequest $request)
    {

        try {
            // Upload icon
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // Create Team
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id,

            ]);

            if (!$team) {
                throw new Exception('Team not created');
            }

            return ResponseFormatter::success($team, 'Team Created');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        try {

            // Search id
            $team = Team::find($id);

            // If id not found
            if (!$team) {
                // throw new Exception('Team not found');
            }

            // Upload logo
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // Update Team
            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon, // ada path gak
                // kalau ada path pakai baru
                //kalau gk ada pakai data yang baru
                'company_id' => $request->company_id,
            ]);

            // Return Response
            return ResponseFormatter::success($team, 'Team Updated');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Get team
            $team = Team::find($id);

            // TODO: Check if team is owned by user

            // Check if team exists
            if (!$team) {
                throw new Exception('Team not found');
            }

            // Delete team
            $team->delete();

            return ResponseFormatter::success('Team deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
