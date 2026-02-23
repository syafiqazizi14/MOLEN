<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Survei;
use Illuminate\Support\Facades\Auth;

class TeamSurveyController extends Controller
{
    // Get KRO List untuk dropdown - Per Team
    public function getKroList()
    {
        $user = Auth::user();
        $teamId = $user->team_id;

        if (!$teamId) {
            return response()->json([]);
        }

        $team = Team::findOrFail($teamId);
        $surveys = $team->available_surveys ?? [];

        // Extract unique KRO dari available_surveys team ini saja
        $kroList = collect($surveys)
            ->pluck('kro')
            ->filter(fn($kro) => $kro !== '' && $kro !== null)
            ->unique()
            ->sort()
            ->values();

        return response()->json($kroList);
    }

    // Simpan Survei Baru (Updated untuk handle KRO)
    public function store(Request $request)
    {
        $request->validate([
            'survey_name' => 'required|string|max:255',
            'kro' => 'required|max:255',
        ]);

        $user = Auth::user();
        $teamId = $user->team_id;

        if (!$teamId) {
            return back()->with('error', 'Anda tidak memiliki tim untuk dikelola.');
        }

        $team = Team::findOrFail($teamId);

        // Ambil array surveys (bisa old format atau new format)
        $currentSurveys = $team->available_surveys ?? [];
        
        // Handle backward compatibility: jika masih array string, convert ke format baru
        if (!empty($currentSurveys) && is_string($currentSurveys[0])) {
            $oldSurveys = $currentSurveys;
            $currentSurveys = [];
            foreach ($oldSurveys as $name) {
                $currentSurveys[] = ['name' => $name, 'kro' => ''];
            }
        }

        // Cek duplikasi berdasarkan nama
        $exists = array_filter($currentSurveys, fn($s) => $s['name'] === $request->survey_name);
        if (!empty($exists)) {
            return back()->with('error', 'Nama survei sudah ada.');
        }

        // Tambah ke array dengan struktur baru
        $currentSurveys[] = [
            'name' => $request->survey_name,
            'kro' => (string) $request->kro
        ];

        // Simpan kembali
        $team->available_surveys = $currentSurveys;
        $team->save();

        return back()->with('success', 'Survei berhasil ditambahkan!');
    }

    // Update Survei (Updated untuk handle KRO)
    public function update(Request $request)
    {
        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string|max:255',
            'kro' => 'required|max:255',
        ]);

        $user = Auth::user();

        if (!$user->team_id) {
            return response()->json(['status' => 'error', 'message' => 'Tim tidak ditemukan'], 404);
        }

        $team = Team::findOrFail($user->team_id);

        // Ambil Data Array
        $surveys = $team->available_surveys;
        if (is_string($surveys)) {
            $surveys = json_decode($surveys, true) ?? [];
        }

        // Handle backward compatibility
        if (!empty($surveys) && is_string($surveys[0])) {
            $oldSurveys = $surveys;
            $surveys = [];
            foreach ($oldSurveys as $name) {
                $surveys[] = ['name' => $name, 'kro' => ''];
            }
        }

        // Cari Index nama lama
        $key = array_search($request->old_name, array_column($surveys, 'name'));

        if ($key !== false) {
            // Update
            $surveys[$key]['name'] = $request->new_name;
            $surveys[$key]['kro'] = (string) $request->kro;

            $team->available_surveys = $surveys;
            $team->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Survei berhasil diperbarui!'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Survei tidak ditemukan.'], 404);
    }

    // Hapus Survei (Updated untuk handle KRO)
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $teamId = $user->team_id;
        $surveyName = $request->survey_name;

        if (!$teamId) return back();

        $team = Team::findOrFail($teamId);
        $currentSurveys = $team->available_surveys ?? [];

        // Handle backward compatibility
        if (!empty($currentSurveys) && is_string($currentSurveys[0])) {
            $oldSurveys = $currentSurveys;
            $currentSurveys = [];
            foreach ($oldSurveys as $name) {
                $currentSurveys[] = ['name' => $name, 'kro' => ''];
            }
        }

        // Hapus berdasarkan nama
        $updatedSurveys = array_values(array_filter($currentSurveys, fn($s) => $s['name'] !== $surveyName));

        $team->available_surveys = $updatedSurveys;
        $team->save();

        return back()->with('success', 'Survei dihapus dari daftar.');
    }
}
