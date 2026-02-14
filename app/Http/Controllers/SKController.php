<?php

namespace App\Http\Controllers;

use App\Models\SK;
use Carbon\Carbon;
use App\Models\Instansi;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class SKController extends Controller
{
    //
    public function SKForm(Request $request)
    {
        // Dapatkan bulan dan tahun sekarang
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Mengonversi bulan numerik ke Romawi
        $bulanRomawi = [
            '01' => 'I',
            '02' => 'II',
            '03' => 'III',
            '04' => 'IV',
            '05' => 'V',
            '06' => 'VI',
            '07' => 'VII',
            '08' => 'VIII',
            '09' => 'IX',
            '10' => 'X',
            '11' => 'XI',
            '12' => 'XII'
        ];

        // Mendapatkan bulan dalam format Romawi untuk query
        $bulanString = $bulanRomawi[str_pad($currentMonth, 2, '0', STR_PAD_LEFT)];

        // Cari nomor transaksi terakhir di tahun yang sama
        $lastNomor = sk::where('tahun', $currentYear)
            ->orderBy('nosurat', 'desc')
            ->first();

        // Set nomor transaksi: Jika tidak ada transaksi, mulai dari 1
        $nomorTransaksi = $lastNomor ? intval($lastNomor->nosurat) + 1 : 1;

        // Format nomor transaksi menjadi 3 digit
        $nomorTransaksi = str_pad($nomorTransaksi, 3, '0', STR_PAD_LEFT);

        // Kirim data ke view
        return view('hamukti.hamuktiskform', [
            'nomorTransaksi' => $nomorTransaksi,
            'bulan' => $bulanString,
            'tahun' => $currentYear,
        ]);
    }


    public function index(Request $request)
    {
        // Set the number of items per page
        $perPage = 10; // Change this number as needed

        // Retrieve the start and end dates from the request
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');
        $searchKeyword = $request->input('search'); // Ambil kata kunci pencarian

        // Initialize the query
        $query = SK::query();

        // Convert input dates to Carbon instances in the correct format
        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();

            // Filter the query based on the provided startDate and endDate
            $query->where('tanggal', '>=', $startDate)
                ->where('tanggal', '<=', $endDate);
        }

        // Apply search filter if keyword is provided
        if ($searchKeyword) {
            $query->where(function ($query) use ($searchKeyword) {
                $query->where('perihal', 'like', '%' . $searchKeyword . '%') // Mencari berdasarkan kegiatan
                    ->orWhere('fungsi', 'like', '%' . $searchKeyword . '%'); // Mencari berdasarkan nama
            });
        }

        // Execute the query, paginate, and retrieve the results
        $sks = $query->orderBy('nosurat', 'desc')->paginate($perPage);

        // Map the results to transform the 'absen' format
        $sks->getCollection()->transform(function ($sk) {
            return [ // yg ada di tampilan tabel
                'perihal' => $sk->perihal,
                'tanggal' => Carbon::parse($sk->tanggal)->translatedFormat('d F Y '),
                'tanggalinput' => Carbon::parse($sk->created_at)->translatedFormat('d F Y H:i:s'),
                'namainstansi' => $sk->namainstansi,
                'fungsi' => $sk->fungsi,
                'nomorfull' => $sk->nomorfull,
                'id' => $sk->id,
            ];
        });

        // Return the view with paginated presences
        return view('hamukti.hamuktisk', [
            'sks' => $sks,
            'pagination' => $sks // Pass the paginated data correctly to the view
        ]);
    }
    public function getSKById($id)
    {
        // Mencari presence berdasarkan ID
        $sk = SK::find($id);

        // Jika event tidak ditemukan
        if (!$sk) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Mengembalikan data event dalam format JSON
        $skData = [
            'perihal' => $sk->perihal,
            'tanggal' => Carbon::parse($sk->tanggal)->translatedFormat('d F Y'), // Menjadi format lokal "09 Oktober 2024 00:56:25"
            'file' => $sk->file,
            'fungsi' => $sk->fungsi,
            'nomorfull' => $sk->nomorfull,
            'id' => $sk->id,
        ];

        return response()->json($skData);
    }
    public function store(Request $request): RedirectResponse
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect()->route('/')->with(['error' => 'Anda harus login terlebih dahulu!']);
        }
 // Format tanggal terima untuk nama file
    $tglTerimaFormatted = Carbon::createFromFormat('m/d/Y', $request->tanggal)->format('dmY');
    // Sanitasi perihal agar aman untuk nama file
    $perihalSanitized = preg_replace('/[^A-Za-z0-9_\- ]/', '', $request->perihal);
    $perihalSanitized = str_replace(' ', '_', $perihalSanitized); // ubah spasi jadi underscore

    $file_name = null;
    
    // Upload file
    if ($request->hasFile('dokumen')) {
        $file = $request->file('dokumen');
        $extension = $file->getClientOriginalExtension();

        // Bentuk nama file baru berdasarkan tanggal terima dan perihal
        $file_name = "{$tglTerimaFormatted}_{$perihalSanitized}.{$extension}";

        // Simpan file ke storage
        $file->storeAs('public/uploads/docs', $file_name);
    } else {
            $file_name = null;
        }

        $tanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $kodekab = $request->input('kodekab');
        // $bulanRomawi = [
        //     '01' => 'I', 
        //     '02' => 'II', 
        //     '03' => 'III', 
        //     '04' => 'IV', 
        //     '05' => 'V', 
        //     '06' => 'VI', 
        //     '07' => 'VII', 
        //     '08' => 'VIII', 
        //     '09' => 'IX', 
        //     '10' => 'X', 
        //     '11' => 'XI', 
        //     '12' => 'XII'
        // ];
        $nomorfull = $request->nosurat . '/' . $kodekab . '/' . $tahun;

        // create product
        SK::create([
            'perihal'         => $request->perihal,
            'tahun'      => $tahun,
            'tanggal'   => Carbon::createFromFormat('m/d/Y', $tanggal)->format('Y-m-d'), // datepicker range start
            'nosurat' => $request->nosurat,
            'nomorfull'   => $nomorfull,
            'file'         => $file_name,
        ]);
        return redirect()->route('hamuktisk.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function delete($id)
    {
        $sk = SK::findOrFail($id);
        $sk->delete();

        // return redirect('agenkitaagenda.getEvents')->with('success', 'SK berhasil dihapus.');
        return redirect()->route('hamuktisk.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function SKFormEdit($id)
    {

        $sk_data = SK::find($id);

        // Jika event tidak ditemukan
        if (!$sk_data) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $sk = [
            'tanggal' => Carbon::parse($sk_data->tanggal)->format('m-d-Y'),
            'bulan' => $sk_data->bulan,
            'tahun' => $sk_data->tahun,
            'nosurat' => $sk_data->nosurat,
            'fungsi' => $sk_data->fungsi,
            'perihal' => $sk_data->perihal,
            'id' => $sk_data->id,

        ];


        // Kirim data ke view
        return view('hamukti.hamuktiskformedit', ['sk' => $sk]);
    }
    public function update(Request $request, $id)
    {
        $schedule= SK::findOrFail($id);

        // Format tanggal terima untuk nama file
    $tglTerimaFormatted = Carbon::createFromFormat('m/d/Y', $request->tanggal)->format('dmY');

    // Sanitasi perihal agar aman untuk nama file
    $perihalSanitized = preg_replace('/[^A-Za-z0-9_\- ]/', '', $request->perihal);
    $perihalSanitized = str_replace(' ', '_', $perihalSanitized);

    // Default: gunakan file lama
    $newFile = $schedule->file;

    // Proses upload file baru (jika ada)
    if ($request->hasFile('dokumen')) {
        $file = $request->file('dokumen');
        $extension = $file->getClientOriginalExtension();

        // Buat nama file baru berdasarkan tgl terima + perihal
        $file_name = "{$tglTerimaFormatted}_{$perihalSanitized}.{$extension}";

        // Hapus file lama jika ada
        if ($schedule->file && Storage::exists('public/uploads/docs/' . $schedule->file)) {
            Storage::delete('public/uploads/docs/' . $sk->file);
        }

        // Simpan file baru
        $file->storeAs('public/uploads/docs', $file_name);

        $newFile = $file_name;
        }

        // Update data schedule
        $schedule->update([
            'perihal'      => $request->perihal,
            'file'       => $newFile, // Gunakan dokumen baru atau yang lama
        ]);
        return redirect()->route('hamuktisk.index')->with(['success' => 'Data Berhasil Diupdate!'], 200);
    }
}
