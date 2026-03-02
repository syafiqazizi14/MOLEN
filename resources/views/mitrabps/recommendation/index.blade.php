@php $title = 'Rekomendasi Bulanan'; @endphp
@include('mitrabps.headerTemp')
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('mitrabps.cuScroll')

<body class="bg-gray-100">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen">
        <x-sidebar></x-sidebar>
        <div class="flex flex-col flex-1 overflow-hidden">
            <x-navbar></x-navbar>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">

                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-speedometer2 text-blue-600"></i> Rekomendasi & Kontrol Honor
                        </h3>
                        <p class="text-gray-500 text-sm">Monitoring pemerataan honor bulan
                            <strong>{{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</strong>.</p>
                    </div>

                    <div class="flex gap-2 items-center">
                        <form method="GET" class="flex gap-2 items-center bg-white p-1 rounded shadow">
                            <select name="month" onchange="this.form.submit()"
                                class="border-none text-sm font-bold text-gray-700 focus:ring-0">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                            <span class="text-gray-300">|</span>
                            <select name="year" onchange="this.form.submit()"
                                class="border-none text-sm font-bold text-gray-700 focus:ring-0">
                                @foreach (range(2023, 2030) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endforeach
                            </select>
                        </form>

                        <a href="{{ route('mitra.penempatan.index') }}"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm flex items-center gap-2 shadow">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="flex gap-4 mb-4 text-xs font-bold text-white">
                    <div class="bg-green-500 px-3 py-1 rounded shadow">Prioritas (< 1 Juta)</div>
                            <div class="bg-yellow-500 px-3 py-1 rounded shadow">Aman (1 - 1.9 Juta)</div>
                            <div class="bg-red-500 px-3 py-1 rounded shadow">Batas Tercapai (≥ 2 Juta)</div>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr class="bg-gray-800 text-white text-sm uppercase">
                                    <th class="py-3 px-6 text-center w-10">Rank</th>
                                    <th class="py-3 px-6 text-left">Nama Mitra</th>
                                    <th class="py-3 px-6 text-center">Status Rekomendasi</th>
                                    <th class="py-3 px-6 text-right">Honor Bulan Ini</th>
                                    <th class="py-3 px-6 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 text-sm">
                                @forelse($paginatedMitras as $index => $mitra)
                                    @php
                                        $income = $mitra->monthly_income;
                                        // Tentukan Zona Warna & Status
                                        if ($income < 1000000) {
                                            $rowClass = 'hover:bg-green-50 border-l-4 border-green-500';
                                            $badge =
                                                "<span class='bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold'>Sangat Direkomendasikan</span>";
                                        } elseif ($income < 2000000) {
                                            $rowClass = 'hover:bg-yellow-50 border-l-4 border-yellow-400';
                                            $badge =
                                                "<span class='bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-bold'>Cukup Direkomendasikan</span>";
                                        } else {
                                            $rowClass = 'hover:bg-red-50 border-l-4 border-red-500 bg-red-50';
                                            $badge =
                                                "<span class='bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold'>Batas Honor Tercapai</span>";
                                        }
                                    @endphp

                                    <tr class="border-b transition duration-150 {{ $rowClass }}">
                                        <td class="py-3 px-6 text-center font-bold text-gray-500">
                                            {{ $paginatedMitras->firstItem() + $index }}
                                        </td>
                                        <td class="py-3 px-6 text-left">
                                            <div class="font-bold text-gray-800">{{ $mitra->nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">{{ $mitra->sobat_id }}</div>
                                        </td>
                                        <td class="py-3 px-6 text-center">
                                            {!! $badge !!}
                                            <div class="text-[10px] text-gray-400 mt-1">{{ $mitra->job_count }}x tugas
                                                bulan ini</div>
                                        </td>
                                        <td class="py-3 px-6 text-right font-mono font-bold text-base">
                                            Rp {{ number_format($income) }}
                                        </td>
                                        <td class="py-3 px-6 text-center">
                                            <button
                                                onclick="checkLimitAndOpenModal({{ $mitra->id_mitra }}, '{{ $mitra->nama_lengkap }}', {{ $income }})"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow text-xs flex items-center gap-1 mx-auto transition transform hover:scale-105">
                                                <i class="bi bi-plus-lg"></i> Beri Tugas
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-gray-500">Data mitra tidak
                                            ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="p-4 bg-gray-50">
                            {{ $paginatedMitras->links() }}
                        </div>
                    </div>

            </main>
        </div>
    </div>

    <div id="assignModal"
        class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
            <div class="bg-blue-600 text-white p-4 rounded-t-lg flex justify-between">
                <h5 class="font-bold">Beri Tugas ke: <span id="modal-mitra-name"></span></h5>
                <button onclick="closeAssignModal()" class="text-white hover:text-gray-200">&times;</button>
            </div>

            <form action="{{ route('placement.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="mitra_id" id="modal-mitra-id">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Bulan Penugasan</label>
                    <select name="month" class="w-full border p-2 rounded bg-gray-100" readonly>
                        <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">*Sesuai bulan filter saat ini</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tim Tujuan</label>
                    <select name="team_id" id="modal-team-select" class="w-full border p-2 rounded" required
                        onchange="updateModalSurveys()">
                        <option value="">-- Pilih Tim --</option>
                        @foreach ($teams as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Survei Utama</label>
                    <div class="flex gap-2">
                        <select name="survey_1" id="modal-survey-1" class="w-2/3 border p-2 rounded text-sm" required>
                            <option value="">-- Pilih Tim Dulu --</option>
                        </select>
                        <input type="number" name="vol_1" value="1"
                            class="w-1/3 border p-2 rounded text-sm text-center" placeholder="Vol">
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeAssignModal()"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const teamSurveys = @json($teamSurveys);

        // [TAMBAHAN BARU] Ambil data User yang sedang login dari Blade
        const userTeamId = {{ auth()->user()->team_id ?? 'null' }};
        const isSuperUser =
            {{ auth()->user()->role == 'admin' || auth()->user()->is_mitra_admin == 1 ? 'true' : 'false' }};

        // FUNGSI CEK LIMIT (Soft Warning)
        function checkLimitAndOpenModal(id, name, currentIncome) {
            // Batas 2 Juta
            if (currentIncome >= 2000000) {
                // Tampilkan Warning Dulu
                Swal.fire({
                    title: 'Peringatan Batas Honor',
                    text: `Mitra ${name} sudah menerima Rp ${new Intl.NumberFormat('id-ID').format(currentIncome)} bulan ini (>= 2 Juta). Apakah Anda yakin ingin menambah tugas lagi?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        openAssignModal(id, name);
                    }
                });
            } else {
                openAssignModal(id, name);
            }
        }

        function openAssignModal(id, name) {
            document.getElementById('assignModal').classList.remove('hidden');
            document.getElementById('modal-mitra-id').value = id;
            document.getElementById('modal-mitra-name').innerText = name;

            // Ambil elemen Select & Form
            const teamSelect = document.getElementById('modal-team-select');
            const formElement = document.querySelector('#assignModal form');

            // Bersihkan input hidden lama jika ada (agar tidak duplikat)
            const oldHidden = document.getElementById('hidden-team-id-fix');
            if (oldHidden) oldHidden.remove();

            // --- LOGIKA PENGUNCIAN TIM ---
            if (userTeamId && !isSuperUser) {
                // MODE TERKUNCI (Ketua Tim)
                teamSelect.value = userTeamId; // 1. Set otomatis ke Tim User
                teamSelect.disabled = true; // 2. Matikan dropdown (user gak bisa ganti)
                teamSelect.classList.add('bg-gray-100', 'text-gray-500'); // 3. Ubah warna jadi abu-abu

                // 4. Buat Input Hidden (PENTING: Agar data team_id tetap terkirim saat disave)
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'team_id';
                hiddenInput.value = userTeamId;
                hiddenInput.id = 'hidden-team-id-fix';
                formElement.appendChild(hiddenInput);

                // 5. Load survei otomatis
                updateModalSurveys();
            } else {
                // MODE BEBAS (Admin)
                teamSelect.value = "";
                teamSelect.disabled = false;
                teamSelect.classList.remove('bg-gray-100', 'text-gray-500');
                document.getElementById('modal-survey-1').innerHTML = '<option value="">-- Pilih Tim Dulu --</option>';
            }
        }

        function closeAssignModal() {
            document.getElementById('assignModal').classList.add('hidden');
        }

        function updateModalSurveys() {
            const teamId = document.getElementById('modal-team-select').value;
            const surveySelect = document.getElementById('modal-survey-1');
            surveySelect.innerHTML = '';

            if (teamId && teamSurveys[teamId]) {
                const surveys = teamSurveys[teamId];
                const list = Array.isArray(surveys) ? surveys : Object.keys(surveys);

                // Tambahkan opsi default
                let defaultOpt = document.createElement('option');
                defaultOpt.value = "";
                defaultOpt.innerText = "-- Pilih Survei --";
                surveySelect.appendChild(defaultOpt);

                list.forEach(s => {
                    let opt = document.createElement('option');
                    opt.value = s;
                    opt.innerHTML = s;
                    surveySelect.appendChild(opt);
                });
            } else {
                surveySelect.innerHTML = '<option value="">-- Pilih Tim Dulu --</option>';
            }
        }
    </script>
</body>
