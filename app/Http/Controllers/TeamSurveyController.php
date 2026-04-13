<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Survei;
use Illuminate\Support\Facades\Auth;

class TeamSurveyController extends Controller
{
    // Get KRO List untuk dropdown - Per Team
    public function getKroList(Request $request)
    {
        $user = Auth::user();
        // Jika ada team_id di request (untuk admin/team 1 yang ingin lihat KRO tim lain)
        $teamId = $request->query('team_id', $user->team_id);

        if (!$teamId) {
            return response()->json([]);
        }

        // Validasi: user hanya bisa lihat KRO jika dia admin, team_id 1, atau itu tim sendiri
        if (!$user->is_admin && $user->team_id != 1 && $user->team_id != $teamId) {
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

    // Simpan Survei Baru (Updated untuk handle KRO dan Tanggal)
    public function store(Request $request)
    {
        $request->validate([
            'survey_name' => 'required|string|max:255',
            'kro' => 'required|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'target_team_id' => 'nullable|integer|exists:teams,id', // Optional: for admin/team 1
        ]);

        $user = Auth::user();
        
        // Tentukan team_id yang ditargetkan
        // Jika admin atau team_id 1, gunakan yang di request, jika tidak gunakan user's team
        if ($user->is_admin || $user->team_id == 1) {
            $teamId = $request->input('target_team_id', $user->team_id);
        } else {
            $teamId = $user->team_id;
        }

        if (!$teamId) {
            return back()->with('error', 'Anda tidak memiliki tim untuk dikelola.');
        }

        // Validasi: user hanya bisa add surveys ke team mereka sendiri ATAU jika admin/team 1
        if (!$user->is_admin && $user->team_id != 1 && $user->team_id != $teamId) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menambah survei ke tim ini.');
        }

        $team = Team::findOrFail($teamId);

        // Ambil array surveys (bisa old format atau new format)
        $currentSurveys = $team->available_surveys ?? [];
        
        // Handle backward compatibility: jika masih array string, convert ke format baru
        if (!empty($currentSurveys) && is_string($currentSurveys[0])) {
            $oldSurveys = $currentSurveys;
            $currentSurveys = [];
            foreach ($oldSurveys as $name) {
                $currentSurveys[] = ['name' => $name, 'kro' => '', 'tanggal_mulai' => null, 'tanggal_selesai' => null];
            }
        }

        // Cek duplikasi berdasarkan nama
        $exists = array_filter($currentSurveys, fn($s) => $s['name'] === $request->survey_name);
        if (!empty($exists)) {
            return back()->with('error', 'Nama survei sudah ada.');
        }

        // Tambah ke array dengan struktur baru (include tanggal)
        $currentSurveys[] = [
            'name' => $request->survey_name,
            'kro' => (string) $request->kro,
            'tanggal_mulai' => $request->filled('tanggal_mulai') ? $request->tanggal_mulai : null,
            'tanggal_selesai' => $request->filled('tanggal_selesai') ? $request->tanggal_selesai : null,
        ];

        // Simpan kembali
        $team->available_surveys = $currentSurveys;
        $team->save();

        return back()->with('success', 'Survei berhasil ditambahkan!');
    }

    // Update Survei (Updated untuk handle KRO dan Tanggal)
    public function update(Request $request)
    {
        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string|max:255',
            'kro' => 'required|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
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
                $surveys[] = ['name' => $name, 'kro' => '', 'tanggal_mulai' => null, 'tanggal_selesai' => null];
            }
        }

        // Cari Index nama lama
        $key = array_search($request->old_name, array_column($surveys, 'name'));

        if ($key !== false) {
            // Update
            $surveys[$key]['name'] = $request->new_name;
            $surveys[$key]['kro'] = (string) $request->kro;
            $surveys[$key]['tanggal_mulai'] = $request->filled('tanggal_mulai') ? $request->tanggal_mulai : null;
            $surveys[$key]['tanggal_selesai'] = $request->filled('tanggal_selesai') ? $request->tanggal_selesai : null;

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
