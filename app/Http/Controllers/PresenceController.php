<?php

namespace App\Http\Controllers;

// use Barryvdh\DomPDF\PDF;
use Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Presence;
use App\Models\Schedule;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PresenceController extends Controller
{
    public function index(Request $request)
    {
        // Set the number of items per page
        $perPage = 10; // Change this number as needed

        // Retrieve the start and end dates from the request
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');
        $searchKeyword = $request->input('search'); // Ambil kata kunci pencarian

        // Initialize the query
        $query = Presence::query();

        // Convert input dates to Carbon instances in the correct format
        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();

            // Filter the query based on the provided startDate and endDate
            $query->where('absen', '>=', $startDate)
                ->where('absen', '<=', $endDate);
        }

        // Apply search filter if keyword is provided
        if ($searchKeyword) {
            $query->where(function ($query) use ($searchKeyword) {
                $query->where('kegiatan', 'like', '%' . $searchKeyword . '%') // Mencari berdasarkan kegiatan
                    ->orWhere('name', 'like', '%' . $searchKeyword . '%'); // Mencari berdasarkan nama
            });
        }

        // Execute the query, paginate, and retrieve the results
        $presences = $query->orderBy('absen', 'desc')->paginate($perPage);
        
          // Maintain query string for filters during pagination
    $presences->appends($request->all());
    // Pastikan locale di-set sebelum format tanggal
            setlocale(LC_ALL, 'id_ID.UTF-8', 'id_ID', 'id'); // Untuk sistem
            Carbon::setLocale('id'); // Untuk Carbon

        // Map the results to transform the 'absen' format
        $presences->getCollection()->transform(function ($presence) {
            return [ // yg ada di tampilan tabel
                'kegiatan' => $presence->kegiatan,
                'absen' => Carbon::parse($presence->absen)->translatedFormat('d F Y H:i:s'),
                'signature' => $presence->signature,
                'lokasi' => $presence->lokasi,
                'jabatan' => $presence->jabatan,
                'name' => $presence->name,
                'id' => $presence->id,
            ];
        });

        // Return the view with paginated presences
        return view('agenkita.agenkitapresensi', [
            'presences' => $presences,
            'pagination' => $presences // Pass the paginated data correctly to the view
        ]);
    }
    public function getEventsPresensi()
    {
        // Mengambil tanggal saat ini
        $today = Carbon::today();
        // dd($today);

        // Mengambil kegiatan yang dimulai dari hari ini atau setelahnya
        $schedules = schedule::where('date_end', '>=', $today)->get();

        // Membuat map dari kegiatan
        $presences = $schedules->map(function ($presence) {
            return [
                'title' => $presence->kegiatan,
                'id' => $presence->id,
            ];
        })->toArray(); // Mengubah hasil map menjadi array

        // Kirim data ke view
        return view('agenkita.agenkitaformpresensi', ['presences' => $presences]);
    }

    public function getEventsPresensiAdmin()
    {


        // Mengambil kegiatan yang dimulai dari hari ini atau setelahnya
        $schedules = schedule::all();
        $users = User::all();
        // Membuat map dari kegiatan
        $presences = $schedules->map(function ($presence) {
            return [
                'title' => $presence->kegiatan,
                'id' => $presence->id,

            ];
        })->toArray(); // Mengubah hasil map menjadi array
        $users = $users->map(function ($user) {
            return [
                'nama' => $user->name,
                'jabatan' => $user->jabatan,
                'id' => $user->id,

            ];
        })->toArray(); // Mengubah hasil map menjadi array

        // Kirim data ke view
        return view('agenkita.agenkitaformpresensiadmin', ['presences' => $presences, 'users' => $users]);
    }
        public function store(Request $request): RedirectResponse
            {
                // Pastikan user sudah login
                if (!auth()->check()) {
                    return redirect()->route('/')->with(['error' => 'Anda harus login terlebih dahulu!']); // diganti path absen
                }
            
                // ✅ Cek apakah user sudah presensi untuk kegiatan yang sama
                $duplikat = Presence::where('user_id', auth()->id())
                    ->where('schedule_id', $request->kegiatan)
                    ->exists();
            
                if ($duplikat) {
                    return redirect()->back()->withInput()->with(['error' => 'Presensi untuk kegiatan ini sudah tercatat!!']); // pesan untuk user
                }
            
                // Mengonversi data URL tanda tangan ke dalam format gambar
                $signatureData = $request->input('signature');
                $image = str_replace('data:image/png;base64,', '', $signatureData);
                $image = str_replace(' ', '+', $image); // Mengganti spasi dengan plus
                $imageName = time() . '.png'; // Nama file untuk tanda tangan dengan format waktu
            
                // Simpan gambar tanda tangan ke dalam folder storage/public/signatures
                Storage::disk('public')->put('uploads/signatures/' . $imageName, base64_decode($image));
            
                // $signature_path = 'uploads/signatures/' . $imageName; // Simpan path gambar tanda tangan
                $signature_path = $imageName; // Simpan path gambar tanda tangan
            
                // $signature = $request->file('ttd'); //ttd diubah nama ttd UI
                // $signature->storeAs('public/uploads/signatures', $signature->hashName());
            
                $idd = $request->kegiatan;
                $id_kegiatan = Schedule::find($idd);
                $nama_kegiatan = $id_kegiatan->kegiatan;
            
                Presence::create([
                    'user_id'       => auth()->id(),
                    'schedule_id'      => $request->kegiatan,
                    'signature'         => $signature_path,
                    'absen'             => Carbon::parse($request->presensidate . ' ' . $request->presensitime),
                    'lokasi' => $request->lokasi, //  starttime
                    'name' => $request->nama, //  endtime
                    'kegiatan'         => $nama_kegiatan,
                    'jabatan'         => $request->jabatan,
                ]);
                return redirect()->route('agenkitapresensi.index')->with(['success' => 'Data Berhasil Disimpan!']); //diganti route presence
            }




    public function deletePresence($id)
    {
        $presence = Presence::findOrFail($id);
        $presence->delete();

        return redirect('agenkitapresensi')->with('success', 'Presensi berhasil dihapus.');
    }

    public function getPresenceById($id)
    {
        // Mencari presence berdasarkan ID
        $presence = Presence::find($id);

        // Jika event tidak ditemukan
        if (!$presence) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Mengembalikan data event dalam format JSON
        $presenceData = [
            'signature' => $presence->signature,
            'absen' => Carbon::parse($presence->absen)->translatedFormat('d F Y H:i:s'), // Menjadi format lokal "09 Oktober 2024 00:56:25"
            'lokasi' => $presence->lokasi,
            'nama' => $presence->name,
            'nip' => optional($presence->user)->nip,
            'kegiatan' => $presence->kegiatan,
            'jabatan' => $presence->jabatan,
            'id' => $presence->id,
        ];

        return response()->json($presenceData);
    }

        public function pdf_export_get(Request $request)
    {
        // 1. MENGAMBIL FILTER DARI REQUEST
        // ===================================
        $searchKeyword = $request->query('search');
        $startDateInput = $request->query('start_date');
        $endDateInput = $request->query('end_date');

        // 2. MEMBUAT QUERY KE DATABASE
        // ============================
        $query = Presence::query();

        // Terapkan filter rentang tanggal jika ada
        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();
            $query->whereBetween('absen', [$startDate, $endDate]);
        }

        // Terapkan filter pencarian kata kunci jika ada
        if ($searchKeyword) {
            $query->where(function ($q) use ($searchKeyword) {
                $q->where('kegiatan', 'like', '%' . $searchKeyword . '%')
                  ->orWhere('name', 'like', '%' . $searchKeyword . '%');
            });
        }

        // Urutkan agar nama tertentu ('Dwi Yuhenny') muncul pertama, sisanya berdasarkan absen
        $presences = $query
            ->orderByRaw("CASE WHEN name = 'Dwi Yuhenny, S.Si, MM' THEN 0 ELSE 1 END") // <-- Urutkan Alya dulu
            ->orderBy('absen', 'asc') // <-- Lalu urutkan sisanya berdasarkan absen terbaru
            ->get();

        // 3. MEMPROSES DATA UNTUK VIEW PDF
        // =================================
        // Pastikan locale di-set sebelum format tanggal
            setlocale(LC_ALL, 'id_ID.UTF-8', 'id_ID', 'id'); // Untuk sistem
            Carbon::setLocale('id'); // Untuk Carbon
        $datas = [];
        foreach ($presences as $presence) {
            // **BAGIAN PENTING UNTUK GAMBAR TANDA TANGAN**
            // Buat path absolut ke file tanda tangan di server
            $signatureAbsolutePath = storage_path('app/public/uploads/signatures/' . $presence->signature);

            // Cek apakah file benar-benar ada untuk menghindari error di PDF
            if (!file_exists($signatureAbsolutePath)) {
                $signatureAbsolutePath = null; // Set null jika file tidak ditemukan
            }

            // Masukkan data yang sudah diproses ke dalam array
            $datas[] = [
                'nama' => $presence->name,
                'nip' => optional($presence->user)->nip,
                'jabatan' => $presence->jabatan,
                'kegiatan' => $presence->kegiatan,
                'absen' => Carbon::parse($presence->absen)->translatedFormat('d F Y H:i:s'),
                'signature_path' => $signatureAbsolutePath, // Ini yang akan digunakan di tag <img> pada view
            ];
        }

        // 4. MEMPERSIAPKAN JUDUL DAN NAMA FILE
        // ======================================
        // Tentukan nama kegiatan untuk judul laporan dan nama file PDF
        if ($presences->isEmpty()) {
            $reportTitle = 'Laporan Presensi'; // Judul default jika tidak ada data
        } else {
            $reportTitle = $presences->first()->kegiatan; // Ambil judul dari data pertama
        }
        
        // Buat nama file yang bersih dari spasi dan karakter aneh
        $filename = str_replace(' ', '_', strtolower($reportTitle)) . '_laporan_absensi.pdf';


        // 5. MEMBUAT DAN MENGUNDUH PDF
        // ==============================
        // Inisialisasi library DomPDF
        $pdf = app('dompdf.wrapper');
        
        // Load view Blade, kirim data yang sudah diproses, dan atur properti kertas
        $pdf->loadView('agenkita.absenrapat', [
                'datas' => $datas, 
                'actt' => $reportTitle
            ])
            ->setPaper('a4', 'landscape');
            
        // Kirim PDF ke browser untuk diunduh
        return $pdf->download($filename);
    }

}
