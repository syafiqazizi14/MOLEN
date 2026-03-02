<table>
    <thead>
        <tr>
            <th colspan="9" style="font-weight: bold; font-size: 14px; text-align: center;">
                REKAP HONOR MITRA - {{ $bulan ? strtoupper($bulan) : 'TAHUN' }} {{ $tahun }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000; text-align: center;">No</th>
            <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000; text-align: center;">Bulan
            </th>
            <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000; text-align: center;">Nama
                Mitra</th>
            <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000; text-align: center;">Sobat
                ID</th>
            <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000; text-align: center;">Tim
            </th>
            <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000; text-align: center;">Posisi
                / Kegiatan</th>
            <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000; text-align: center;">Volume
            </th>
            <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000; text-align: center;">Rate
                (Satuan)</th>
            <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000; text-align: center;">Total
                Honor</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($placements as $index => $p)
            @php
                // --- LOGIKA PERHITUNGAN KHUSUS (Berbasis 3 Slot Survei) ---

                $listKegiatan = [];
                $listVolume = [];
                $listRate = [];
                $totalHonor = 0;

                // Loop 3 kali karena struktur tabel ada survey_1 s.d survey_3
                for ($i = 1; $i <= 3; $i++) {
                    $colSurvey = 'survey_' . $i; // survey_1, survey_2, ...
                    $colVol = 'vol_' . $i; // vol_1, vol_2, ...

                    $namaSurvei = $p->$colSurvey;
                    $volume = $p->$colVol;

                    if (!empty($namaSurvei)) {
                        // 1. Simpan Nama & Volume
                        $listKegiatan[] = $namaSurvei;
                        $listVolume[] = $volume;

                        // 2. Cari Rate (Cost) di Tabel Rates sesuai Gambar Anda
                        // Pastikan Model Rate sudah ada (App\Models\Rate)
                        $rateObj = \App\Models\Rate::where('team_id', $p->team_id)
                            ->where('survey_name', $namaSurvei)
                            ->where('month', $p->month)
                            ->where('year', $p->year)
                            ->first();

                        $hargaSatuan = $rateObj->cost ?? 0; // Ambil kolom 'cost'

                        // 3. Hitung Subtotal & Masukkan ke List
                        // Tambahkan 'Rp ' agar Excel membacanya sebagai teks/mata uang yang benar
                        $listRate[] = 'Rp ' . number_format($hargaSatuan, 0, ',', '.');
                        $totalHonor += $volume * $hargaSatuan;
                    }
                }

                // Gabungkan array jadi string pakai koma (jika ada lebih dari 1 survei)
                $strKegiatan = implode(', ', $listKegiatan);
                $strVolume = implode(', ', $listVolume);
                $strRate = implode(', ', $listRate);
            @endphp

            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $p->month }}</td>
                <td style="border: 1px solid #000;">{{ $p->mitra->nama_lengkap ?? '-' }}</td>
                <td style="border: 1px solid #000;">'{{ $p->mitra->sobat_id ?? '-' }}</td>
                <td style="border: 1px solid #000;">{{ $p->team->name ?? '-' }}</td>

                <td style="border: 1px solid #000;">{{ $strKegiatan ?: '-' }}</td>

                <td style="border: 1px solid #000; text-align: center;">{{ $strVolume ?: '0' }}</td>

                <td style="border: 1px solid #000; text-align: right;">{{ $strRate ?: '-' }}</td>

                <td style="border: 1px solid #000; text-align: right; font-weight: bold;">
                    Rp {{ number_format($totalHonor, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
