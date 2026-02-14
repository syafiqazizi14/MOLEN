@php $title = 'Detail Honor Mitra'; @endphp
@include('mitrabps.headerTemp')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.tailwindcss.com"></script>
@include('mitrabps.cuScroll')

<body class="bg-gray-100">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen">
        <x-sidebar></x-sidebar>
        <div class="flex flex-col flex-1 overflow-hidden">
            <x-navbar></x-navbar>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">{{ $mitra->nama_lengkap }}</h3>
                        <p class="text-gray-500 text-sm">SOBAT ID: {{ $mitra->sobat_id }}</p>
                    </div>
                    <div class="flex gap-2 items-center">
                        <form method="GET" class="mr-4">
                            <select name="year" onchange="this.form.submit()" class="border p-2 rounded shadow-sm">
                                @foreach (range(2023, 2030) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endforeach
                            </select>
                        </form>
                        <a href="{{ route('mitra.rekap.index') }}?year={{ $year }}"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="space-y-4">
                    @php $grandTotal = 0; @endphp

                    @foreach (range(1, 12) as $m)
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="bg-gray-50 px-4 py-2 border-b flex justify-between items-center">
                                <h4 class="font-bold text-gray-700 uppercase">{{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </h4>
                                @if (!isset($placements[$m]))
                                    <span class="text-xs text-gray-400 italic">Tidak ada kegiatan</span>
                                @endif
                            </div>

                            @if (isset($placements[$m]))
                                <div class="p-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                                <tr class="text-left text-gray-500 border-b">
                                                    <th class="pb-2 w-1/6">Tim</th>
                                                    <th class="pb-2 w-1/3">Nama Survei</th>
                                                    <th class="pb-2 w-1/6 text-center">Harga Satuan</th>
                                                    <th class="pb-2 w-1/6 text-center">Volume (Edit)</th>
                                                    <th class="pb-2 w-1/6 text-right">Subtotal</th>
                                                    <th class="pb-2 w-10"></th>
                                                </tr>
                                            </thead>

                                            @foreach ($placements[$m] as $p)
                                                @php
                                                    // Hitung subtotal untuk display awal
                                                    $h1 = $rates[$p->survey_1] ?? 0;
                                                    $sub1 = $h1 * $p->vol_1;

                                                    $h2 = $p->survey_2 ? $rates[$p->survey_2] ?? 0 : 0;
                                                    $sub2 = $h2 * $p->vol_2;

                                                    $h3 = $p->survey_3 ? $rates[$p->survey_3] ?? 0 : 0;
                                                    $sub3 = $h3 * $p->vol_3;

                                                    $totalRow = $sub1 + $sub2 + $sub3;
                                                    $grandTotal += $totalRow;
                                                @endphp

                                                <tbody id="row-{{ $p->id }}"
                                                    class="border-b last:border-0 hover:bg-gray-50 group">

                                                    <tr>
                                                        <td class="py-2 font-bold text-blue-600">{{ $p->team->name }}
                                                        </td>
                                                        <td class="py-2">{{ $p->survey_1 }}</td>
                                                        <td class="py-2 text-center text-gray-500">
                                                            {{ number_format($h1) }}</td>
                                                        <td class="py-2 text-center">
                                                            <input type="number" name="vol_1"
                                                                value="{{ $p->vol_1 }}"
                                                                onkeydown="checkEnter(event, {{ $p->id }})"
                                                                class="w-16 border rounded text-center p-1 text-sm bg-yellow-50 focus:bg-white focus:ring-2 focus:ring-blue-200">
                                                        </td>
                                                        <td class="py-2 text-right font-bold">
                                                            {{ number_format($sub1) }}</td>
                                                        <td class="py-2 text-right align-top" rowspan="3">
                                                            <button type="button"
                                                                onclick="submitVol({{ $p->id }})"
                                                                class="btn-save text-blue-600 hover:text-blue-800 p-2"
                                                                title="Simpan Perubahan">
                                                                <i class="bi bi-save text-lg"></i>
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    @if ($p->survey_2)
                                                        <tr class="bg-gray-50/50">
                                                            <td class="py-2"></td>
                                                            <td class="py-2">{{ $p->survey_2 }}</td>
                                                            <td class="py-2 text-center text-gray-500">
                                                                {{ number_format($h2) }}</td>
                                                            <td class="py-2 text-center">
                                                                <input type="number" name="vol_2"
                                                                    value="{{ $p->vol_2 }}"
                                                                    onkeydown="checkEnter(event, {{ $p->id }})"
                                                                    class="w-16 border rounded text-center p-1 text-sm bg-yellow-50 focus:bg-white">
                                                            </td>
                                                            <td class="py-2 text-right font-bold">
                                                                {{ number_format($sub2) }}</td>
                                                        </tr>
                                                    @endif

                                                    @if ($p->survey_3)
                                                        <tr class="bg-gray-50/50">
                                                            <td class="py-2"></td>
                                                            <td class="py-2">{{ $p->survey_3 }}</td>
                                                            <td class="py-2 text-center text-gray-500">
                                                                {{ number_format($h3) }}</td>
                                                            <td class="py-2 text-center">
                                                                <input type="number" name="vol_3"
                                                                    value="{{ $p->vol_3 }}"
                                                                    onkeydown="checkEnter(event, {{ $p->id }})"
                                                                    class="w-16 border rounded text-center p-1 text-sm bg-yellow-50 focus:bg-white">
                                                            </td>
                                                            <td class="py-2 text-right font-bold">
                                                                {{ number_format($sub3) }}</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div
                        class="fixed bottom-6 right-6 bg-gray-900 text-white p-4 rounded-lg shadow-xl border-l-4 border-green-500 z-50">
                        <div class="text-xs text-gray-400 uppercase">Total Honor {{ $year }}</div>
                        <div class="text-2xl font-bold font-mono">Rp {{ number_format($grandTotal) }}</div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script>
        // Fungsi mendeteksi tombol Enter
        function checkEnter(e, id) {
            if (e.key === "Enter") {
                e.preventDefault();
                submitVol(id);
            }
        }

        // Fungsi Utama Simpan
        function submitVol(placementId, forceSave = 0) {
            // 1. Ambil Container Baris (TBODY)
            const container = document.getElementById('row-' + placementId);
            const btn = container.querySelector('.btn-save');
            const originalIcon = btn.innerHTML;

            // 2. Ambil Nilai Input
            const v1 = container.querySelector('input[name="vol_1"]').value;
            // Cek input vol_2/3 (karena mungkin tidak ada di baris tsb)
            const v2Input = container.querySelector('input[name="vol_2"]');
            const v3Input = container.querySelector('input[name="vol_3"]');
            const v2 = v2Input ? v2Input.value : 0;
            const v3 = v3Input ? v3Input.value : 0;

            // 3. Siapkan Data
            let formData = new FormData();

            // [PERBAIKAN PENTING] Tambahkan Method PUT agar Route Laravel menerimanya
            formData.append('_method', 'PUT');

            formData.append('placement_id', placementId);
            formData.append('vol_1', v1);
            formData.append('vol_2', v2);
            formData.append('vol_3', v3);
            formData.append('force_save', forceSave);

            // 4. UI Loading
            btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i>';
            btn.disabled = true;

            // 5. Kirim AJAX
            const url = "{{ route('mitra.rekap.update') }}";

            fetch(url, {
                    method: 'POST', // Tetap POST, tapi Laravel akan membacanya sebagai PUT karena ada _method
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    btn.innerHTML = originalIcon;
                    btn.disabled = false;

                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });

                    } else if (data.status === 'warning') {
                        // WARNING 4 JUTA
                        Swal.fire({
                            title: 'Peringatan Batas Honor',
                            text: data.message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Lanjut Simpan!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Panggil diri sendiri lagi dengan force_save = 1
                                submitVol(placementId, 1);
                            }
                        });

                    } else {
                        Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                    }
                })
                .catch(error => {
                    console.error(error);
                    btn.innerHTML = originalIcon;
                    btn.disabled = false;
                    Swal.fire('Error', 'Gagal menghubungi server. Cek Console (F12) tab Network.', 'error');
                });
        }
    </script>
</body>
