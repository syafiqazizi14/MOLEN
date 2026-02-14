<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;

class TeamSurveyController extends Controller
{
    // Simpan Survei Baru (Sudah Benar)
    public function store(Request $request)
    {
        $request->validate([
            'survey_name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $teamId = $user->team_id;

        if (!$teamId) {
            return back()->with('error', 'Anda tidak memiliki tim untuk dikelola.');
        }

        $team = Team::findOrFail($teamId);

        // Ambil array lama
        $currentSurveys = $team->available_surveys ?? [];

        // Cek duplikasi
        if (in_array($request->survey_name, $currentSurveys)) {
            return back()->with('error', 'Nama survei sudah ada.');
        }

        // Tambah ke array
        $currentSurveys[] = $request->survey_name;

        // Simpan kembali
        $team->available_surveys = $currentSurveys;
        $team->save();

        return back()->with('success', 'Survei berhasil ditambahkan!');
    }

    // Update Survei (Disesuaikan untuk JSON Array)
    public function update(Request $request)
    {
        // 1. Validasi Input (Kita butuh nama lama & nama baru)
        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        if (!$user->team_id) {
            return response()->json(['status' => 'error', 'message' => 'Tim tidak ditemukan'], 404);
        }

        $team = Team::findOrFail($user->team_id);

        // 2. Ambil Data Array dari kolom available_surveys
        // Pastikan model Team sudah ada casts 'array', jika belum kita decode manual
        $surveys = $team->available_surveys;
        if (is_string($surveys)) {
            $surveys = json_decode($surveys, true) ?? [];
        }

        // 3. Cari Index nama lama di dalam array
        $key = array_search($request->old_name, $surveys);

        if ($key !== false) {
            // 4. Update nama di index tersebut
            $surveys[$key] = $request->new_name;

            // 5. Simpan kembali ke database
            $team->available_surveys = $surveys;
            $team->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Nama survei berhasil diperbarui!'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Survei lama tidak ditemukan.'], 404);
    }

    // Hapus Survei (Sudah Benar)
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $teamId = $user->team_id;
        $surveyName = $request->survey_name;

        if (!$teamId) return back();

        $team = Team::findOrFail($teamId);
        $currentSurveys = $team->available_surveys ?? [];

        // Hapus item dari array
        $updatedSurveys = array_values(array_diff($currentSurveys, [$surveyName]));

        $team->available_surveys = $updatedSurveys;
        $team->save();

        return back()->with('success', 'Survei dihapus dari daftar.');
    }
}
