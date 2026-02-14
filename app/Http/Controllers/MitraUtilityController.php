<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mitra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MitraUtilityController extends Controller
{
    // 1. Tampilkan Halaman Upload
    public function index()
    {
        // CEK OTORITAS: Hanya Admin & Tim IPDS (ID 6)
        $user = Auth::user();
        if ($user->is_mitra_admin != 1 && $user->team_id != 6) {
            return redirect()->back()->with('error', 'Akses Ditolak! Fitur ini khusus Admin & Tim IPDS.');
        }

        return view('mitrabps.utility.update_status');
    }

    // 2. Proses Import CSV (Logika Incremental / Penambahan)
    public function updateStatus(Request $request)
    {
        // CEK OTORITAS
        $user = Auth::user();
        if ($user->is_mitra_admin != 1 && $user->team_id != 6) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah data ini.');
        }

        // 1. Validasi
        $request->validate([
            'file_csv' => 'required|file',
            'status_target' => 'nullable' // Bisa 'Mitra Rutin', 'Mitra Sensus', atau kosong
        ]);

        $file = $request->file('file_csv');
        $target = $request->status_target;

        // 2. Deteksi Delimiter (Titik koma atau Koma)
        $firstLine = fgets(fopen($file->getPathname(), 'r'));
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

        $handle = fopen($file->getPathname(), "r");

        $updated = 0;
        $processed = 0;

        DB::beginTransaction();
        try {
            // [PERUBAHAN LOGIKA DI SINI]
            // SAYA MENGHAPUS LOGIKA RESET.
            // Status mitra yang lama TIDAK AKAN DIHAPUS.
            // Sistem hanya akan mengupdate mitra yang Sobat ID-nya ada di dalam file Excel ini.

            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $rawId = $data[0] ?? '';
                // Bersihkan ID dari karakter aneh
                $sobatId = preg_replace('/[^0-9]/', '', $rawId);

                if (empty($sobatId) || strlen($sobatId) < 4) continue;

                $mitra = Mitra::where('sobat_id', (string)$sobatId)->first();

                if ($mitra) {
                    // Update status mitra ini saja.
                    // Jika sebelumnya 'Mitra Rutin' dan sekarang diupload 'Mitra Sensus', 
                    // dia akan berubah jadi 'Mitra Sensus'. Mitra lain AMAN.
                    $mitra->jenis_mitra = $target;
                    $mitra->save();
                    $updated++;
                }

                $processed++;
            }

            DB::commit();
            fclose($handle);

            if ($updated == 0) {
                return back()->with('error', "Gagal! Tidak ada SOBAT ID yang cocok di database. Pastikan format CSV benar.");
            }

            // Pesan Sukses Baru
            return back()->with('success', "Sukses! $updated mitra berhasil di-update statusnya menjadi '$target'. Data mitra lainnya TETAP AMAN (tidak berubah/terhapus).");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error Sistem: ' . $e->getMessage());
        }
    }
}
