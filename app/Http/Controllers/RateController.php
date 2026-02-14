<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rate;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;

class RateController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. FILTER WAKTU (Default bulan ini)
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        // 2. OTORITAS
        $isAdmin = $user->is_mitra_admin == 1;
        $isLeader = !is_null($user->team_id);

        // 3. QUERY DATA RATE (Filter Waktu + Otoritas)
        $query = Rate::with('team')
            ->where('month', $month) // <--- Penting: Filter Bulan
            ->where('year', $year);  // <--- Penting: Filter Tahun

        // Jika Ketua Tim (dan bukan admin), hanya tampilkan rate timnya sendiri
        if ($isLeader && !$isAdmin) {
            $query->where('team_id', $user->team_id);
        }

        // Filter dropdown (jika admin memilih filter)
        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        $rates = $query->orderBy('team_id')->get();

        // 4. DATA TIM
        if ($isAdmin) {
            $teams = Team::all();
        } else {
            $teams = Team::where('id', $user->team_id)->get();
        }

        // 5. DATA JSON SURVEI
        $teamSurveys = [];
        foreach ($teams as $t) {
            $surveys = $t->available_surveys ?? [];
            if (is_string($surveys)) {
                $surveys = json_decode($surveys, true) ?? [];
            }
            $teamSurveys[$t->id] = $surveys;
        }

        return view('mitrabps.rates.index', compact(
            'rates',
            'teams',
            'teamSurveys',
            'isAdmin',
            'isLeader',
            'month',
            'year' // <--- Kirim month & year
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'team_id' => 'required',
            'survey_name' => 'required',
            'month' => 'required',
            'year' => 'required',
            'cost' => 'required|numeric|min:0',
            'unit' => 'required'
        ]);

        $user = Auth::user();
        if (!$user->is_mitra_admin && $user->team_id != $request->team_id) {
            return back()->with('error', 'Anda hanya berhak mengatur harga tim Anda sendiri.');
        }

        // SIMPAN SPESIFIK BULAN & TAHUN
        Rate::updateOrCreate(
            [
                'team_id' => $request->team_id,
                'survey_name' => $request->survey_name,
                'month' => $request->month, // Kunci unik
                'year' => $request->year    // Kunci unik
            ],
            [
                'cost' => $request->cost,
                'unit' => $request->unit
            ]
        );

        return back()->with('success', 'Standar harga berhasil disimpan!');
    }

    // --- UPDATE HARGA (INLINE EDIT) ---
    public function update(Request $request, $id)
    {
        $rate = Rate::findOrFail($id);

        $user = Auth::user();
        if (!$user->is_mitra_admin && $user->team_id != $rate->team_id) {
            return back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'cost' => 'required|numeric|min:0',
        ]);

        $rate->cost = $request->cost;
        $rate->save(); // Bulan & Tahun tidak berubah

        return back()->with('success', 'Harga berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $rate = Rate::findOrFail($id);

        $user = Auth::user();
        if (!$user->is_mitra_admin && $user->team_id != $rate->team_id) {
            return back()->with('error', 'Akses ditolak.');
        }

        $rate->delete();
        return back()->with('success', 'Harga honor dihapus.');
    }
}
