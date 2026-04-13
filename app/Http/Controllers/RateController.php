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
        $isLeader = !is_null($user->team_id) && $user->team_id != 1;
        $canManageAllTeams = $isAdmin || $user->team_id == 1;

        // 3. QUERY DATA RATE (Filter Waktu + Otoritas)
        $query = Rate::with('team')
            ->where('month', $month) // <--- Penting: Filter Bulan
            ->where('year', $year);  // <--- Penting: Filter Tahun

        // Jika Ketua Tim (dan bukan admin/team 1), hanya tampilkan rate timnya sendiri
        if ($isLeader && !$canManageAllTeams) {
            $query->where('team_id', $user->team_id);
        }

        // Filter dropdown (jika admin memilih filter)
        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        $rates = $query->orderBy('team_id')->get();

        // 3.5 GROUP & SUM rates by (team_id, survey_name, unit)
        $groupedRates = $rates->groupBy(function($item) {
            return $item->team_id . '|' . $item->survey_name . '|' . $item->unit;
        })->map(function($group) {
            $first = $group->first();
            $totalCost = $group->sum('cost');
            $itemCount = $group->count();
            
            return (object) [
                'id' => $first->id,
                'team_id' => $first->team_id,
                'team' => $first->team,
                'survey_name' => $first->survey_name,
                'unit' => $first->unit,
                'cost' => $totalCost,
                'originalCount' => $itemCount,
                'items' => $group->toArray()
            ];
        })->values();

        // 4. DATA TIM
        if ($canManageAllTeams) {
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

        $rates = $groupedRates;

        return view('mitrabps.rates.index', compact(
            'rates',
            'teams',
            'teamSurveys',
            'isAdmin',
            'isLeader',
            'canManageAllTeams',
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
        $requestedTeamId = $request->input('team_id');
        
        // AUTHORIZATION CHECK:
        // - Admin atau team_id 1 bisa set harga ke tim manapun
        // - Regular user hanya bisa ke tim mereka sendiri
        if (!$user->is_admin && $user->team_id != 1) {
            if ($user->team_id != $requestedTeamId) {
                return back()->with('error', 'Anda hanya berhak mengatur harga tim Anda sendiri.');
            }
        }

        // CEK APAKAH SUDAH ADA RATE DENGAN (team_id, survey_name, unit, month, year) YANG SAMA
        $existingRate = Rate::where('team_id', $request->team_id)
            ->where('survey_name', $request->survey_name)
            ->where('unit', $request->unit)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($existingRate) {
            // JIKA SUDAH ADA: TAMBAHKAN COST KE YANG SUDAH ADA
            $existingRate->cost += $request->cost;
            $existingRate->save();
        } else {
            // JIKA BELUM ADA: BUAT RECORD BARU
            Rate::create([
                'team_id' => $request->team_id,
                'survey_name' => $request->survey_name,
                'unit' => $request->unit,
                'cost' => $request->cost,
                'month' => $request->month,
                'year' => $request->year
            ]);
        }

        return back()->with('success', 'Standar harga berhasil disimpan!');
    }

    // --- UPDATE HARGA (INLINE EDIT) ---
    public function update(Request $request, $id)
    {
        $rate = Rate::findOrFail($id);

        $user = Auth::user();
        // Allow: admin, team_id 1, or owner of the rate's team
        if (!$user->is_admin && $user->team_id != 1 && $user->team_id != $rate->team_id) {
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
        // Allow: admin, team_id 1, or owner of the rate's team
        if (!$user->is_admin && $user->team_id != 1 && $user->team_id != $rate->team_id) {
            return back()->with('error', 'Akses ditolak.');
        }

        $rate->delete();
        return back()->with('success', 'Harga honor dihapus.');
    }

    // --- UPDATE GROUPED RATES ---
    public function updateGrouped(Request $request)
    {
        $request->validate([
            'team_id' => 'required',
            'survey_name' => 'required',
            'unit' => 'required',
            'month' => 'required',
            'year' => 'required',
            'cost' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $requestedTeamId = $request->input('team_id');

        if (!$user->is_admin && $user->team_id != 1) {
            if ($user->team_id != $requestedTeamId) {
                return back()->with('error', 'Anda hanya berhak mengatur harga tim Anda sendiri.');
            }
        }

        $updatedCount = Rate::where('team_id', $request->team_id)
            ->where('survey_name', $request->survey_name)
            ->where('unit', $request->unit)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->update(['cost' => $request->cost]);

        return back()->with('success', "Standar harga berhasil diperbarui! ($updatedCount item)");
    }

    // --- DELETE GROUPED RATES ---
    public function destroyGrouped(Request $request)
    {
        $request->validate([
            'team_id' => 'required',
            'survey_name' => 'required',
            'unit' => 'required',
            'month' => 'required',
            'year' => 'required',
        ]);

        $user = Auth::user();
        $requestedTeamId = $request->input('team_id');

        if (!$user->is_admin && $user->team_id != 1) {
            if ($user->team_id != $requestedTeamId) {
                return back()->with('error', 'Anda hanya berhak menghapus harga tim Anda sendiri.');
            }
        }

        $deletedCount = Rate::where('team_id', $request->team_id)
            ->where('survey_name', $request->survey_name)
            ->where('unit', $request->unit)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->delete();

        return back()->with('success', "Harga honor dihapus. ($deletedCount item)");
    }
}
