@php
    $title = 'Atur Standar Honor';
    $currentMonth = $month ?? date('n');
    $currentYear = $year ?? date('Y');
@endphp

@include('mitrabps.headerTemp')

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    div.swal2-container {
        z-index: 20001 !important;
    }
</style>

@include('mitrabps.cuScroll')

<body class="bg-gray-100">
    @if (session('success'))
        <script>
            Swal.fire("Sukses", "{{ session('success') }}", "success");
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire("Gagal", "{{ session('error') }}", "error");
        </script>
    @endif

    <div x-data="{ sidebarOpen: false }" class="flex h-screen">
        <x-sidebar></x-sidebar>
        <div class="flex flex-col flex-1 overflow-hidden">
            <x-navbar></x-navbar>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">

                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-gray-800">Standar Honor Kegiatan</h3>

                    <div class="flex gap-2 items-center">
                        <form method="GET" class="flex gap-2 bg-white p-2 rounded shadow border border-gray-200">
                            <select name="month" onchange="this.form.submit()"
                                class="text-sm font-bold border-none focus:ring-0 cursor-pointer">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-gray-300">|</span>
                            <select name="year" onchange="this.form.submit()"
                                class="text-sm font-bold border-none focus:ring-0 cursor-pointer">
                                @foreach (range(2023, 2030) as $y)
                                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        <button onclick="openRateModal()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm flex items-center gap-2 shadow">
                            <i class="bi bi-plus-lg"></i> Tambah Harga
                        </button>
                        <a href="{{ route('mitra.rekap.index') }}"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm shadow">
                            Kembali
                        </a>
                    </div>
                </div>

                <div
                    class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-3 rounded mb-4 text-sm flex justify-between items-center shadow-sm">
                    <span><i class="bi bi-info-circle-fill mr-1"></i> Periode:
                        <b>{{ date('F', mktime(0, 0, 0, $currentMonth, 1)) }} {{ $currentYear }}</b></span>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-gray-800 text-white text-sm uppercase">
                                <th class="py-3 px-6 text-left">Tim</th>
                                <th class="py-3 px-6 text-left">Nama Survei</th>
                                <th class="py-3 px-6 text-left">Harga Satuan (Edit)</th>
                                <th class="py-3 px-6 text-center">Satuan</th>
                                <th class="py-3 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @forelse($rates as $rate)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-6 font-bold text-blue-600">{{ $rate->team->name ?? '-' }}</td>
                                    <td class="py-3 px-6 font-semibold">{{ $rate->survey_name }}</td>

                                    <td class="py-3 px-6">
                                        <form action="{{ route('mitra.rates.update', $rate->id) }}" method="POST"
                                            class="flex items-center gap-2">
                                            @csrf @method('PUT')
                                            <div class="relative w-full">
                                                <span class="absolute left-2 top-1.5 text-gray-500 text-xs">Rp</span>
                                                <input type="number" name="cost" value="{{ $rate->cost }}"
                                                    class="w-full border border-gray-300 rounded pl-8 pr-2 py-1 text-right font-mono focus:ring-blue-500 text-sm"
                                                    required>
                                            </div>
                                            <button type="submit"
                                                class="bg-green-500 text-white p-1.5 rounded hover:bg-green-600 shadow"
                                                title="Simpan">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>

                                    <td class="py-3 px-6 text-center text-gray-500">{{ $rate->unit }}</td>

                                    <td class="py-3 px-6 text-center">
                                        <form action="{{ route('mitra.rates.destroy', $rate->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button" onclick="confirmDeleteRate(this)"
                                                class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded transition shadow-sm mx-auto flex items-center justify-center"
                                                title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500 bg-gray-50">Belum ada data
                                        harga.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <div id="rateModal"
        class="fixed inset-0 z-[10000] hidden overflow-y-auto bg-gray-900 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
            <div class="bg-blue-600 text-white p-4 rounded-t-lg flex justify-between">
                <h5 class="font-bold">Tambah Harga Survei</h5>
                <button onclick="closeRateModal()" class="text-white hover:text-gray-200 text-2xl">&times;</button>
            </div>
            <form action="{{ route('mitra.rates.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="month" value="{{ $currentMonth }}">
                <input type="hidden" name="year" value="{{ $currentYear }}">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tim</label>
                    <select name="team_id" id="modal-team-select"
                        class="w-full border p-2 rounded {{ $isLeader && !$isAdmin ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                        onchange="updateSurveyDropdown()" required>
                        @if (!$isLeader || $isAdmin)
                            <option value="">-- Pilih Tim --</option>
                        @endif
                        @foreach ($teams as $t)
                            <option value="{{ $t->id }}"
                                {{ $isLeader && Auth::user()->team_id == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}</option>
                        @endforeach
                    </select>
                    @if ($isLeader && !$isAdmin)
                        <input type="hidden" name="team_id" value="{{ Auth::user()->team_id }}">
                        <script>
                            document.getElementById('modal-team-select').disabled = true;
                        </script>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Survei</label>
                    <select name="survey_name" id="modal-survey-select" class="w-full border p-2 rounded" required>
                        <option value="">-- Pilih Tim Dulu --</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Harga (Rp)</label>
                        <input type="number" name="cost" class="w-full border p-2 rounded text-right"
                            placeholder="0" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Satuan</label>
                        <input type="text" name="unit" class="w-full border p-2 rounded"
                            placeholder="Contoh: Dokumen" required>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="button" onclick="closeRateModal()"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 font-bold text-gray-700">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Data dari Controller (Safe Mode)
        const teamSurveys = @json($teamSurveys ?? []);
        const isLeader = @json($isLeader && !$isAdmin);

        // --- FUNGSI MODAL ---
        function openRateModal() {
            document.getElementById('rateModal').classList.remove('hidden');
            if (isLeader) updateSurveyDropdown();
        }

        function closeRateModal() {
            document.getElementById('rateModal').classList.add('hidden');
        }

        function updateSurveyDropdown() {
            const teamId = document.getElementById('modal-team-select').value;
            const surveySelect = document.getElementById('modal-survey-select');
            surveySelect.innerHTML = '<option value="">-- Pilih Survei --</option>';

            if (teamId && teamSurveys[teamId]) {
                const surveys = teamSurveys[teamId];
                const list = Array.isArray(surveys) ? surveys : Object.keys(surveys);
                list.forEach(s => {
                    let opt = document.createElement('option');
                    opt.value = s;
                    opt.innerHTML = s;
                    surveySelect.appendChild(opt);
                });
            }
        }

        // --- FUNGSI HAPUS RATE DENGAN SWEETALERT ---
        function confirmDeleteRate(button) {
            Swal.fire({
                title: 'Hapus Standar Honor?',
                text: "Data harga ini akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    </script>
</body>
