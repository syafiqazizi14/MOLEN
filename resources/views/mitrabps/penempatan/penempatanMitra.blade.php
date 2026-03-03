@php
    $title = 'Penempatan Mitra';
    // Pastikan variabel ada nilai defaultnya jika controller lupa mengirim
    $currentMonth = $month ?? date('n');
    $currentYear = $year ?? date('Y');
@endphp

@include('mitrabps.headerTemp')
<style>
    /* MEMAKSA SWEETALERT MUNCUL PALING DEPAN */
    /* Karena Modal Form kita z-index nya 10000, maka Swal harus lebih besar */
    div.swal2-container {
        z-index: 20001 !important;
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('mitrabps.cuScroll')
<style>
    /* MEMAKSA SWEETALERT MUNCUL PALING DEPAN */
    /* Karena Modal Form kita z-index nya 10000, maka Swal harus lebih besar */
    div.swal2-container {
        z-index: 20001 !important;
    }
</style>

<body class="bg-gray-100 font-sans leading-normal tracking-normal">

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
    @if ($errors->any())
        <script>
            Swal.fire("Error", "{{ $errors->first() }}", "error");
        </script>
    @endif

    <div x-data="{ sidebarOpen: false }" class="flex h-screen">
        <x-sidebar></x-sidebar>

        <div class="flex flex-col flex-1 overflow-hidden">
            <x-navbar></x-navbar>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">

                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-gray-800">
                        Alokasi Mitra ({{ date('F', mktime(0, 0, 0, $currentMonth, 1)) }} {{ $currentYear }})
                    </h3>

                    <div class="flex gap-2 flex-wrap justify-end">
                        <a href="{{ route('mitra.rekap.index') }}"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow flex items-center gap-2 text-sm">
                            <i class="bi bi-cash-coin"></i> Rekap Honor
                        </a>
                        <a href="{{ route('mitra.planning.index') }}"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded shadow flex items-center gap-2 text-sm">
                            <i class="bi bi-calendar-week"></i> Perencanaan
                        </a>
                        <a href="{{ route('mitra.recommendation.index') }}"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded shadow flex items-center gap-2 text-sm">
                            <i class="bi bi-stars"></i> Rekomendasi
                        </a>
                        {{-- TOMBOL KELOLA SURVEI (KHUSUS KETUA TIM) --}}
                        @if (Auth::user()->team_id && !Auth::user()->is_mitra_admin)
                            <button onclick="openSurveyModal()"
                                class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded shadow flex items-center gap-2 text-sm transition duration-200">
                                <i class="bi bi-list-check"></i> Kelola Survei
                            </button>
                        @endif

                        @if (auth()->user()->is_mitra_admin == 1 || auth()->user()->team_id == 6)
                            <a href="{{ route('mitra.status.form') }}"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm flex items-center gap-2 shadow transition duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Update Status Mitra
                            </a>
                        @endif

                        {{-- TOMBOL TAMBAH MITRA --}}
                        @if (isset($canEdit) ? $canEdit : true)
                            <button type="button" onclick="openAssignModal()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow flex items-center gap-2 transition duration-200 text-sm">
                                <i class="bi bi-person-plus-fill"></i> Tambah Mitra
                            </button>
                        @endif
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg shadow mb-6">
                    <form method="GET" action="{{ route('mitra.penempatan.index') }}"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="text-xs font-bold text-gray-600 uppercase">Bulan</label>
                            <select name="month" onchange="this.form.submit()"
                                class="w-full border border-gray-300 p-2 rounded focus:ring-blue-500 text-sm">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-600 uppercase">Tahun</label>
                            <select name="year" onchange="this.form.submit()"
                                class="w-full border border-gray-300 p-2 rounded focus:ring-blue-500 text-sm">
                                @foreach (range(2023, 2030) as $y)
                                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-600 uppercase">Filter Tim</label>
                            <select name="filter_team_id" onchange="this.form.submit()"
                                class="w-full border border-gray-300 p-2 rounded focus:ring-blue-500 text-sm">
                                <option value="">Semua Tim</option>
                                @foreach ($teams as $t)
                                    <option value="{{ $t->id }}"
                                        {{ request('filter_team_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-600 uppercase">Cari Nama</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Ketik nama mitra..."
                                class="w-full border border-gray-300 p-2 rounded focus:ring-blue-500 text-sm">
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                                    <th class="py-3 px-6 text-left font-bold w-1/4">Nama Mitra</th>

                                    <th class="py-3 px-6 text-center font-bold w-1/5">Tim</th>
                                    <th class="py-3 px-6 text-left font-bold w-1/3">Detail Tugas</th>
                                    @if ($canEdit)
                                        <th class="py-3 px-6 text-center font-bold">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                @forelse($mitras as $m)
                                    <tr
                                        class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 align-top">
                                        <td class="py-4 px-6 text-left">
                                            <div class="font-bold text-gray-800">{{ $m->nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500 mt-1">ID: {{ $m->sobat_id ?? '-' }}</div>
                                        </td>

                                        <td class="py-4 px-6 text-center">
                                            <div class="flex flex-col gap-2 items-center">
                                                @foreach ($m->placements as $p)
                                                    @if ($p->status_anggota == 'BKO')
                                                        <span
                                                            class="bg-yellow-100 text-yellow-800 border border-yellow-200 px-3 py-1 rounded-full text-[10px] font-bold shadow-sm"
                                                            title="Mitra Sementara di Bulan Ini">
                                                            {{ $p->team->name ?? '-' }} <span
                                                                class="text-[9px] ml-1"></span>
                                                        </span>
                                                    @else
                                                        <span
                                                            class="bg-blue-100 text-blue-800 border border-blue-200 px-3 py-1 rounded-full text-[10px] font-bold shadow-sm">
                                                            {{ $p->team->name ?? '-' }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </td>

                                        <td class="py-4 px-6 text-left">
                                            <div class="flex flex-col gap-3">
                                                @foreach ($m->placements as $p)
                                                    <div class="border-b pb-2 last:border-0 border-gray-100">
                                                        <div class="font-bold text-gray-700 text-xs flex items-center">
                                                            <i class="bi bi-dot"></i> {{ $p->survey_1 }}
                                                            <span
                                                                class="bg-gray-200 text-gray-700 px-1.5 ml-1 rounded text-[10px]">v:{{ $p->vol_1 }}</span>
                                                        </div>
                                                        @if ($p->survey_2)
                                                            <div class="text-[10px] ml-4 text-gray-500 mt-0.5">+
                                                                {{ $p->survey_2 }} (v:{{ $p->vol_2 }})</div>
                                                        @endif
                                                        @if ($p->survey_3)
                                                            <div class="text-[10px] ml-4 text-gray-500 mt-0.5">+
                                                                {{ $p->survey_3 }} (v:{{ $p->vol_3 }})</div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>

                                        @if ($canEdit)
                                            <td class="py-4 px-6 text-center align-middle">
                                                <div
                                                    class="flex flex-col gap-3 items-center justify-center h-full w-full">
                                                    @foreach ($m->placements as $p)
                                                        <div class="flex items-center gap-2">

                                                            @php
                                                                // LOGIKA HAK AKSES EDIT/HAPUS
                                                                // 1. Admin boleh segalanya
                                                                // 2. Ketua Tim hanya boleh edit/hapus jika ID Tim Tugas == ID Tim User
                                                                $user = Auth::user();
                                                                $isMyTask =
                                                                    $user->is_mitra_admin == 1 ||
                                                                    $user->team_id == $p->team_id;
                                                            @endphp

                                                            @if ($isMyTask)
                                                                {{-- TOMBOL EDIT --}}
                                                                <a href="{{ route('mitra.planning.index', ['month' => $currentMonth, 'year' => $currentYear, 'edit_placement' => $p->id]) }}"
                                                                    class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-full transition duration-200 shadow-sm flex items-center justify-center"
                                                                    title="Edit tugas {{ $p->team->name }}">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="h-4 w-4" fill="none"
                                                                        viewBox="0 0 24 24" stroke="currentColor"
                                                                        stroke-width="2">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </a>

                                                                {{-- TOMBOL HAPUS --}}
                                                                <form action="{{ route('placement.destroy', $p->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button type="button"
                                                                        onclick="confirmDeleteTask(this, '{{ $p->team->name }}')"
                                                                        class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-1.5 rounded-full transition duration-200 shadow-sm flex items-center justify-center"
                                                                        title="Hapus tugas {{ $p->team->name }}">

                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="h-4 w-4" fill="none"
                                                                            viewBox="0 0 24 24" stroke="currentColor"
                                                                            stroke-width="2">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <span
                                                                    class="text-gray-300 p-1.5 cursor-not-allowed flex items-center justify-center"
                                                                    title="Bukan wewenang Anda">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="h-4 w-4" fill="none"
                                                                        viewBox="0 0 24 24" stroke="currentColor"
                                                                        stroke-width="2">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                                    </svg>
                                                                </span>
                                                            @endif

                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-500 bg-gray-50">
                                            <div class="flex flex-col items-center">
                                                <i class="bi bi-inbox text-4xl mb-2 text-gray-300"></i>
                                                <p>Belum ada mitra yang ditugaskan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
    
    {{-- KIRI: info jumlah data --}}
    <div class="text-sm text-gray-600">
        Menampilkan
        <span class="font-semibold">{{ $mitras->firstItem() ?? 0 }}</span>
        –
        <span class="font-semibold">{{ $mitras->lastItem() ?? 0 }}</span>
        dari
        <span class="font-semibold">{{ $mitras->total() }}</span>
        data
    </div>

    {{-- KANAN: pagination --}}
    <div>
        {{ $mitras->links() }}
    </div>

</div>

                </div>

            </main>
        </div>
    </div>

    <div id="assignModal" class="fixed inset-0 z-[9999] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">

        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" onclick="closeAssignModal()"></div>

        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
            <div
                class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full z-[10000]">

                <div class="bg-blue-600 text-white p-4 flex justify-between items-center">
                    <h5 class="font-bold text-lg">Tambah Mitra ke Tim</h5>
                    <button type="button" onclick="closeAssignModal()"
                        class="text-white hover:text-gray-200 text-2xl font-bold">&times;</button>
                </div>

                <form id="assignment-form" action="{{ route('placement.store') }}" method="POST" class="p-6">
                    @csrf

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-xs font-bold mb-1 uppercase">Bulan</label>
                            <select name="month" id="modal_month" class="w-full border p-2 rounded text-sm">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-xs font-bold mb-1 uppercase">Tahun</label>
                            <select name="year" id="modal_year" class="w-full border p-2 rounded text-sm"
                                onchange="checkLockOnYearChange()">
                                @foreach (range(2023, 2030) as $y)
                                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-xs font-bold mb-1 uppercase">Cari Mitra <span
                                class="text-red-500">*</span></label>
                        <select id="mitra-select" name="mitra_id" placeholder="Ketik nama..." required>
                            <option value="">Cari nama...</option>
                            @foreach ($mitraList as $m)
                                <option value="{{ $m->id_mitra }}">{{ $m->nama_lengkap }} ({{ $m->sobat_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-xs font-bold mb-1 uppercase">Pilih Tim <span
                                class="text-red-500">*</span></label>

                        @php
                            // Cek apakah user adalah Ketua Tim (Bukan Admin)
                            $isLeader = Auth::user()->team_id && !Auth::user()->is_mitra_admin;
                            $userTeamId = Auth::user()->team_id;
                        @endphp

                        <select name="team_id" id="modal-team-select"
                            class="w-full border p-2 rounded text-sm {{ $isLeader ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                            required onchange="updateModalSurveys()" {{ $isLeader ? 'disabled' : '' }}>

                            <option value="">-- Pilih Tim --</option>

                            @foreach ($teams as $t)
                                <option value="{{ $t->id }}" {{-- Jika Ketua Tim, otomatis select tim dia --}}
                                    {{ $isLeader && $userTeamId == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>

                        @if ($isLeader)
                            <input type="hidden" name="team_id" value="{{ $userTeamId }}">
                        @endif

                        <div id="lock-msg" class="hidden mt-2 p-2 rounded text-xs"></div>
                    </div>

                    <!-- SURVEI DINAMIS CONTAINER -->
                    <div id="surveys-container" class="space-y-3 mb-4 max-h-64 overflow-y-auto border border-gray-200 rounded p-3 bg-white">
                        <!-- Survey akan di-insert di sini -->
                    </div>

                    <!-- TOMBOL TAMBAH SURVEI -->
                    <button type="button" onclick="addSurveyField()" id="btn-add-survey"
                        class="w-full mb-4 bg-green-50 hover:bg-green-100 text-green-700 border border-green-300 px-3 py-2 rounded-md text-sm font-semibold transition duration-200 flex items-center justify-center gap-2">
                        <i class="bi bi-plus-circle"></i> Tambah Survei Tambahan
                    </button>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" onclick="closeAssignModal()"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-gray-700 text-sm font-bold">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-bold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="surveyModal" class="fixed inset-0 z-[9999] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" onclick="closeSurveyModal()"></div>

        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
            <div
                class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md sm:w-full z-[10000]">

                <div class="bg-teal-600 text-white p-4 flex justify-between items-center">
                    <h5 class="font-bold text-lg">Daftar Survei Tim Saya</h5>
                    <button type="button" onclick="closeSurveyModal()"
                        class="text-white hover:text-gray-200 text-2xl font-bold">&times;</button>
                </div>

                <div class="p-6">
                    <form action="{{ route('team.surveys.store') }}" method="POST" class="mb-6 bg-gradient-to-br from-teal-50 to-cyan-50 p-4 rounded-lg border border-teal-200">
                        @csrf
                        <h6 class="text-sm font-bold text-teal-900 mb-4 pb-3 border-b border-teal-200">Tambah Survei Baru</h6>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Survei</label>
                                <input type="text" name="survey_name"
                                    class="w-full border border-gray-300 p-2.5 rounded-md text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    placeholder="Contoh: Susenas Maret" required>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">KRO (Kode Responden)</label>
                                <select name="kro" id="kro-dropdown"
                                    class="w-full border border-gray-300 p-2.5 rounded-md text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    placeholder="Pilih atau input KRO..." required>
                                    <option value="">-- Pilih KRO atau input baru --</option>
                                </select>
                            </div>

                            <div class="pt-1">
                                <label class="block text-xs font-semibold text-gray-700 mb-2.5">Jadwal Kegiatan</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-600 block mb-1">Tanggal Mulai</label>
                                        <input type="date" name="tanggal_mulai"
                                            class="w-full border border-gray-300 p-2 rounded-md text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-600 block mb-1">Tanggal Selesai</label>
                                        <input type="date" name="tanggal_selesai"
                                            class="w-full border border-gray-300 p-2 rounded-md text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full mt-4 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2.5 rounded-md text-sm font-bold transition duration-200 shadow-sm">
                            Tambah Survei
                        </button>
                    </form>

                    <div class="border-t border-gray-200 my-6"></div>

                    <h6 class="text-sm font-bold text-gray-800 mb-4">Daftar Survei Tersedia</h6>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-3 max-h-64 overflow-y-auto custom-scrollbar space-y-2">
                        @php
                            $myTeamSurveys = [];
                            if (Auth::user()->team_id) {
                                $teamObj = \App\Models\Team::find(Auth::user()->team_id);
                                // Handle array/json manual agar aman
                                $raw = $teamObj->available_surveys;
                                $myTeamSurveys = is_string($raw) ? json_decode($raw, true) : $raw ?? [];
                                
                                // Handle backward compatibility: jika masih array string, convert ke format baru
                                if (!empty($myTeamSurveys) && is_string($myTeamSurveys[0])) {
                                    $oldSurveys = $myTeamSurveys;
                                    $myTeamSurveys = [];
                                    foreach ($oldSurveys as $name) {
                                        $myTeamSurveys[] = ['name' => $name, 'kro' => ''];
                                    }
                                }
                            }
                        @endphp

                        @if (count($myTeamSurveys) > 0)
                            @foreach ($myTeamSurveys as $index => $s)
                                <div class="bg-white rounded-lg border border-gray-300 p-4 hover:shadow-md transition duration-200 survey-item-{{ $index }}">
                                    <!-- View Mode -->
                                    <div class="survey-text-{{ $index }}">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-gray-900 text-sm">{{ $s['name'] }}</h4>
                                            </div>
                                            @if($s['kro'] ?? false)
                                                <span class="ml-2 inline-block bg-teal-100 text-teal-800 text-xs font-semibold px-2.5 py-1 rounded-full">{{ $s['kro'] }}</span>
                                            @endif
                                        </div>
                                        
                                        @php
                                            $tglMulaiFormatted = !empty($s['tanggal_mulai']) ? \Carbon\Carbon::parse($s['tanggal_mulai'])->format('d/m/Y') : '-';
                                            $tglSelesaiFormatted = !empty($s['tanggal_selesai']) ? \Carbon\Carbon::parse($s['tanggal_selesai'])->format('d/m/Y') : '-';
                                        @endphp
                                        @if(!empty($s['tanggal_mulai']) || !empty($s['tanggal_selesai']))
                                            <div class="schedule-display-{{ $index }} text-xs text-gray-600 mt-2 pt-2 border-t border-gray-200">
                                                <span class="text-gray-700 font-medium">Jadwal:</span> {{ $tglMulaiFormatted }} - {{ $tglSelesaiFormatted }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Edit Mode (Hidden) -->
                                    <div class="survey-edit-{{ $index }} hidden space-y-3">
                                        <div>
                                            <label class="text-xs text-gray-600 font-semibold block mb-1">Nama Survei</label>
                                            <input type="text" id="survey-input-{{ $index }}"
                                                value="{{ $s['name'] }}" data-original="{{ $s['name'] }}"
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                                placeholder="Nama Survei" disabled
                                                onkeydown="if(event.key === 'Enter') updateSurveyName('{{ $index }}')"/>
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-600 font-semibold block mb-1">KRO</label>
                                            <input type="text" id="kro-input-{{ $index }}"
                                                value="{{ $s['kro'] ?? '' }}"
                                                data-original="{{ $s['kro'] ?? '' }}"
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                                placeholder="KRO" disabled/>
                                        </div>
                                        <div class="schedule-input-{{ $index }} grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-xs text-gray-600 font-semibold block mb-1">Mulai</label>
                                                <input type="date" id="tgl-mulai-{{ $index }}"
                                                    value="{{ $s['tanggal_mulai'] ?? '' }}"
                                                    data-original="{{ $s['tanggal_mulai'] ?? '' }}"
                                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                                    disabled/>
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-600 font-semibold block mb-1">Selesai</label>
                                                <input type="date" id="tgl-selesai-{{ $index }}"
                                                    value="{{ $s['tanggal_selesai'] ?? '' }}"
                                                    data-original="{{ $s['tanggal_selesai'] ?? '' }}"
                                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                                    disabled/>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex gap-2 mt-3 pt-3 border-t border-gray-200">
                                        <button type="button" id="btn-edit-{{ $index }}" onclick="toggleEditMode('{{ $index }}')"
                                            class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-2 rounded-md text-xs font-semibold border border-blue-200 transition duration-200">
                                            Edit
                                        </button>
                                        <button type="button" id="btn-save-{{ $index }}" onclick="updateSurveyName('{{ $index }}')" style="display: none;"
                                            class="flex-1 bg-green-50 hover:bg-green-100 text-green-700 px-3 py-2 rounded-md text-xs font-semibold border border-green-200 transition duration-200">
                                            Simpan
                                        </button>
                                        <form id="form-hapus-{{ $loop->index }}"
                                            action="{{ route('team.surveys.destroy') }}" method="POST" class="flex-1">
                                            @csrf
                                            <input type="hidden" name="survey_name" value="{{ $s['name'] }}">
                                            <button type="button"
                                                onclick="konfirmasiHapus('form-hapus-{{ $loop->index }}')"
                                                class="w-full bg-red-50 hover:bg-red-100 text-red-700 px-3 py-2 rounded-md text-xs font-semibold border border-red-200 transition duration-200">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-6">
                                <p class="text-sm text-gray-400">Belum ada survei. Tambahkan survei baru di atas.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        // ==========================================
        // 1. DATA & VARIABEL GLOBAL
        // ==========================================
        const teamSurveys = @json($teamSurveys ?? []);
        const mitraLocks = @json($mitraLocks ?? []);
        const pageYear = "{{ $year ?? date('Y') }}";

        // Status User (Apakah Ketua Tim?)
        const isUserLeader = @json(Auth::user()->team_id && !Auth::user()->is_mitra_admin);
        const userTeamId = @json(Auth::user()->team_id);

        let tomSelectInstance = null;
        let currentLockedTeams = []; // Array untuk menyimpan tim utama mitra

        // ==========================================
        // 2. FUNGSI MODAL KELOLA SURVEI (YANG HILANG)
        // ==========================================
        function openSurveyModal() {
            const modal = document.getElementById('surveyModal');
            if (modal) {
                modal.classList.remove('hidden');
                // Load KRO list dan init Tom Select
                loadKroList();
            } else {
                console.error("Modal Survey tidak ditemukan di HTML");
            }
        }

        function closeSurveyModal() {
            const modal = document.getElementById('surveyModal');
            if (modal) modal.classList.add('hidden');
        }

        // Load KRO list dari API dan init Tom Select
        function loadKroList() {
            const kroDropdown = document.getElementById('kro-dropdown');
            if (!kroDropdown) return;

            // Fetch KRO list dari API
            fetch("{{ route('api.kro.list') }}")
                .then(response => response.json())
                .then(data => {
                    // Clear existing options (except the placeholder)
                    kroDropdown.innerHTML = '<option value="">-- Pilih KRO atau input baru --</option>';
                    
                    // Add KRO options
                    data.forEach(kro => {
                        const option = document.createElement('option');
                        option.value = kro;
                        option.textContent = kro;
                        kroDropdown.appendChild(option);
                    });

                    // Destroy existing Tom Select instance if any
                    if (kroDropdown.tomselect) {
                        kroDropdown.tomselect.destroy();
                    }

                    // Init Tom Select with create mode enabled
                    new TomSelect(kroDropdown, {
                        create: true,
                        maxItems: 1,
                        placeholder: 'Pilih atau input KRO...',
                        searchField: 'text',
                        sortField: {
                            field: 'text',
                            direction: 'asc'
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading KRO list:', error);
                });
        }

        // ==========================================
        // 3. FUNGSI MODAL TAMBAH MITRA (ASSIGN)
        // ==========================================
        function openAssignModal() {
            document.getElementById('assignModal').classList.remove('hidden');

            // Reset form dan inisialisasi survei pertama
            document.getElementById('assignment-form').reset();
            
            // Clear dan reset container survei
            const container = document.getElementById('surveys-container');
            container.innerHTML = '';
            surveyFieldCount = 0;
            
            // Tambah satu survei kosong
            addSurveyField();
            
            // Inisialisasi button state
            updateAddSurveyButtonState();

            // Init TomSelect jika belum ada
            if (!tomSelectInstance) {
                try {
                    tomSelectInstance = new TomSelect("#mitra-select", {
                        create: false,
                        sortField: {
                            field: "text",
                            direction: "asc"
                        },
                        placeholder: "Ketik nama mitra...",
                        onChange: function(value) {
                            checkMitraLock(value);
                        }
                    });
                } catch (e) {
                    console.error("TomSelect Error:", e);
                }
            } else {
                tomSelectInstance.clear();
            }

            // Khusus Ketua Tim: Auto Select Tim Sendiri
            if (isUserLeader) {
                const teamSelect = document.getElementById('modal-team-select');
                if (teamSelect) {
                    teamSelect.value = userTeamId;
                    updateModalSurveys(); // Load survei langsung
                    updateAllSurveySelects(); // Pastikan dropdowns di-update
                }
            }
        }

        function closeAssignModal() {
            document.getElementById('assignModal').classList.add('hidden');
        }

        // ==========================================
        // 4. LOGIKA CEK DATA MITRA & LOCK TIM
        // ==========================================
        function checkMitraLock(mitraId) {
            if (!mitraId) return;

            const selectedYear = document.getElementById('modal_year').value;
            const teamSelect = document.getElementById('modal-team-select');

            currentLockedTeams = []; // Reset

            // Ambil data lock dari controller
            if (mitraLocks.hasOwnProperty(mitraId)) {
                let rawData = mitraLocks[mitraId];
                // Pastikan format array
                let lockedData = Array.isArray(rawData) ? rawData : [rawData];

                // Cek Tahun (Hanya kunci jika tahun di modal == tahun halaman)
                if (selectedYear == pageYear) {
                    currentLockedTeams = lockedData;
                }
            }

            // Logika Auto-Select (Hanya untuk Admin, Ketua Tim sudah di-handle di openAssignModal)
            if (!isUserLeader) {
                if (currentLockedTeams.length === 1) {
                    teamSelect.value = currentLockedTeams[0]; // Pilih otomatis jika baru 1 tim
                } else if (currentLockedTeams.length >= 2) {
                    teamSelect.value = ""; // Kosongkan jika sudah 2 tim (biar user mikir/pilih)
                } else {
                    teamSelect.value = "";
                }
            }

            updateModalSurveys();
        }

        function checkLockOnYearChange() {
            const mitraId = document.getElementById('mitra-select').value;
            if (mitraId) checkMitraLock(mitraId);
        }

        // ==========================================
        // 5. FUNGSI MANAJEMEN SURVEI DINAMIS
        // ==========================================
        let surveyFieldCount = 0;
        
        function addSurveyField() {
            const container = document.getElementById('surveys-container');
            const currentCount = document.querySelectorAll('.survey-field').length;
            
            // Validasi: maksimal 3 survei
            if (currentCount >= 3) {
                Swal.fire('Peringatan', 'Maksimal 3 survei untuk setiap mitra!', 'warning');
                return;
            }
            
            surveyFieldCount++;
            const index = surveyFieldCount;
            
            const label = index === 1 ? 'Survei Utama' : `Survei Tambahan ${index - 1}`;
            const required = index === 1 ? 'required' : '';
            
            const html = `
                <div class="bg-gray-50 p-3 rounded border survey-field" id="survey-field-${index}">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase">${label}</label>
                        ${index > 1 ? `<button type="button" onclick="removeSurveyField(${index})" class="text-red-500 hover:text-red-700 text-sm font-bold">Hapus</button>` : ''}
                    </div>
                    <div class="flex gap-2">
                        <select name="survey_${index}" id="modal-survey-${index}" class="modal-survey-select w-3/4 border p-2 rounded text-sm" ${required}>
                            <option value="">-- Pilih Tim Dahulu --</option>
                        </select>
                        <input type="number" name="vol_${index}" value="1"
                            class="w-1/4 border p-2 rounded text-sm text-center" placeholder="Vol" min="1">
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
            
            // Update survei buat field baru ini
            updateAllSurveySelects();
            
            // Update status tombol
            updateAddSurveyButtonState();
        }
        
        function removeSurveyField(index) {
            document.getElementById(`survey-field-${index}`).remove();
            updateAddSurveyButtonState();
        }
        
        function updateAddSurveyButtonState() {
            const button = document.getElementById('btn-add-survey');
            const currentCount = document.querySelectorAll('.survey-field').length;
            
            if (currentCount >= 3) {
                button.disabled = true;
                button.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        
        function updateAllSurveySelects() {
            const teamId = parseInt(document.getElementById('modal-team-select').value);
            const selects = document.querySelectorAll('.modal-survey-select');
            
            selects.forEach(select => {
                select.innerHTML = '<option value="">-- Pilih Tim Dahulu --</option>';
                
                if (teamId && teamSurveys[teamId]) {
                    const surveys = teamSurveys[teamId];
                    const list = Array.isArray(surveys) ? surveys : Object.keys(surveys);
                    list.forEach(s => {
                        let opt = document.createElement('option');
                        // Handle both old (string) and new (array) format
                        if (typeof s === 'string') {
                            opt.value = s;
                            opt.innerHTML = s;
                        } else if (s && s.name) {
                            opt.value = s.name;
                            opt.innerHTML = s.name + (s.kro ? ' [' + s.kro + ']' : '');
                        }
                        select.appendChild(opt);
                    });
                }
            });
        }

        // ==========================================
        // 6. UPDATE TAMPILAN & PESAN WARNING
        // ==========================================
        function updateModalSurveys() {
            const teamId = parseInt(document.getElementById('modal-team-select').value);
            const lockMsg = document.getElementById('lock-msg');

            // Reset UI
            lockMsg.classList.add('hidden');
            lockMsg.className = "hidden mt-2 p-2 rounded text-xs";

            // Update semua survey select fields
            updateAllSurveySelects();

            // Tampilkan Pesan Status / Warning
            if (teamId) {
                const isExistingTeam = currentLockedTeams.includes(teamId);
                const count = currentLockedTeams.length;

                if (isExistingTeam) {
                    // Mitra sudah ada di tim ini
                    lockMsg.innerHTML = `<i class="bi bi-check-circle-fill"></i> <b>Tim Utama Terdaftar:</b> Status Tetap.`;
                    lockMsg.classList.remove('hidden');
                    lockMsg.classList.add('bg-blue-100', 'text-blue-800');
                } else {
                    // Mitra belum ada di tim ini
                    if (count < 2) {
                        lockMsg.innerHTML =
                            `<i class="bi bi-plus-circle-fill"></i> <b>Slot Tersedia:</b> Akan menjadi Tim Utama ke-${count + 1} (Status: Tetap).`;
                        lockMsg.classList.remove('hidden');
                        lockMsg.classList.add('bg-green-100', 'text-green-800');
                    } else {
                        lockMsg.innerHTML =
                            `<i class="bi bi-exclamation-triangle-fill"></i> <b>Batas Tim Utama Penuh (2/2):</b> Mitra akan berstatus BKO/Sementara.`;
                        lockMsg.classList.remove('hidden');
                        lockMsg.classList.add('bg-yellow-100', 'text-yellow-800');
                    }
                }
            }
        }

        // ==========================================
        // 7. INTERCEPTOR SUBMIT FORM (POPUP KONFIRMASI)
        // ==========================================
        const formAssign = document.getElementById('assignment-form');
        if (formAssign) {
            formAssign.addEventListener('submit', function(e) {
                // Validasi: SEMUA survei yang ditampilkan harus punya nama
                let surveyValidation = [];
                document.querySelectorAll('.modal-survey-select').forEach((select, idx) => {
                    const index = idx + 1;
                    const surveyName = select.value;
                    const volInput = select.parentElement.querySelector('input[type="number"]');
                    const volume = volInput ? volInput.value : 1;
                    
                    surveyValidation.push({
                        index: index,
                        survey: surveyName,
                        volume: volume
                    });
                    
                    console.log(`Survey ${index}: ${surveyName} (volume: ${volume})`);
                });
                
                // Cek minimal ada 1 survei yang dipilih
                let hasSurvey = surveyValidation.some(s => s.survey);
                
                if (!hasSurvey) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Peringatan',
                        text: 'Pilih minimal 1 survei untuk mitra ini',
                        icon: 'warning'
                    });
                    return;
                }
                
                // Cek jika ada survei yang kosong di tengah (harus urut)
                for (let i = 0; i < surveyValidation.length; i++) {
                    if (!surveyValidation[i].survey && i > 0) {
                        // Ada gap di survei - suruh user isi dari atas
                        e.preventDefault();
                        Swal.fire({
                            title: 'Peringatan',
                            text: 'Survei harus diisi dari atas. Jangan ada yang kosong di tengah.',
                            icon: 'warning'
                        });
                        return;
                    }
                }
                
                const teamSelect = document.getElementById('modal-team-select');
                const selectedTeamId = parseInt(teamSelect.value);

                // Buat Input Hidden status_anggota
                let statusInput = document.getElementById('status_anggota');
                if (!statusInput) {
                    statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status_anggota';
                    statusInput.id = 'status_anggota';
                    this.appendChild(statusInput);
                }

                const isExistingTeam = currentLockedTeams.includes(selectedTeamId);

                // Trigger Warning HANYA JIKA slot penuh (>=2) DAN memilih tim baru
                if (!isExistingTeam && currentLockedTeams.length >= 2) {
                    e.preventDefault(); // Tahan Submit

                    Swal.fire({
                        title: 'Batas Tim Utama Tercapai',
                        html: `Mitra ini sudah memiliki 2 Tim Utama.<br>Tambahkan sebagai <b>BKO (Sementara)</b>?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Jadikan BKO',
                        cancelButtonText: 'Batal, Ganti Tim'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            statusInput.value = 'BKO'; // Set BKO
                            formAssign.submit(); // Lanjut Kirim
                        }
                        // Jika Batal, diam saja (Form tetap terbuka)
                    });
                } else {
                    statusInput.value = 'Tetap'; // Set Tetap
                    // Biarkan submit berjalan normal
                }
            });
        }
        // --- FUNGSI HAPUS SURVEI DENGAN SWEETALERT ---
        function confirmDeleteSurvey(button) {
            Swal.fire({
                title: 'Hapus Survei?',
                text: "Nama survei ini akan dihapus dari daftar.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // Merah
                cancelButtonColor: '#3085d6', // Biru
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Cari form terdekat dari tombol yang diklik, lalu submit
                    button.closest('form').submit();
                }
            });
        }
        // --- FUNGSI HAPUS TUGAS DENGAN SWEETALERT ---
        function confirmDeleteTask(button, teamName) {
            Swal.fire({
                title: 'Hapus Penugasan?',
                html: "Anda akan menghapus tugas di <b>" + teamName +
                    "</b>.<br>Data yang dihapus tidak dapat dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // Merah
                cancelButtonColor: '#3085d6', // Biru
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form terdekat
                    button.closest('form').submit();
                }
            });
        }

        function updateSurveyName(index) {
            const input = document.getElementById('survey-input-' + index);
            const kroInput = document.getElementById('kro-input-' + index);
            const tglMulaiInput = document.getElementById('tgl-mulai-' + index);
            const tglSelesaiInput = document.getElementById('tgl-selesai-' + index);
            const newName = input.value;
            const oldName = input.getAttribute('data-original');
            const newKro = kroInput.value;
            const newTglMulai = tglMulaiInput.value;
            const newTglSelesai = tglSelesaiInput.value;
            const oldKro = kroInput.getAttribute('data-original') || '';
            const oldTglMulai = tglMulaiInput.getAttribute('data-original') || '';
            const oldTglSelesai = tglSelesaiInput.getAttribute('data-original') || '';
            const editBtn = document.getElementById('btn-edit-' + index);
            const saveBtn = document.getElementById('btn-save-' + index);

            if (newName === oldName && newKro === oldKro && newTglMulai === oldTglMulai && newTglSelesai === oldTglSelesai) {
                Swal.fire('Info', 'Belum ada perubahan yang disimpan.', 'info');
                return;
            }

            // UI Loading
            saveBtn.innerText = '...';
            input.disabled = true;
            kroInput.disabled = true;
            tglMulaiInput.disabled = true;
            tglSelesaiInput.disabled = true;
            saveBtn.disabled = true;

            fetch("{{ route('team.surveys.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        old_name: oldName,
                        new_name: newName,
                        kro: newKro,
                        tanggal_mulai: newTglMulai || null,
                        tanggal_selesai: newTglSelesai || null
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update data-original agar sinkron jika mau edit lagi tanpa refresh
                        input.setAttribute('data-original', newName);
                        kroInput.setAttribute('data-original', newKro);
                        tglMulaiInput.setAttribute('data-original', newTglMulai);
                        tglSelesaiInput.setAttribute('data-original', newTglSelesai);

                        // Feedback Visual (Border Hijau)
                        input.classList.add('border-green-500', 'ring-1', 'ring-green-500');
                        kroInput.classList.add('border-green-500', 'ring-1', 'ring-green-500');
                        tglMulaiInput.classList.add('border-green-500', 'ring-1', 'ring-green-500');
                        tglSelesaiInput.classList.add('border-green-500', 'ring-1', 'ring-green-500');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Survei, KRO, dan tanggal berhasil diperbarui!',
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            // Disable fields dan toggle buttons kembali ke mode view
                            input.disabled = true;
                            kroInput.disabled = true;
                            tglMulaiInput.disabled = true;
                            tglSelesaiInput.disabled = true;
                            
                            editBtn.style.display = 'block';
                            saveBtn.style.display = 'none';
                            
                            location.reload();
                        });
                    } else {
                        saveBtn.innerText = 'Simpan';
                        input.disabled = false;
                        kroInput.disabled = false;
                        tglMulaiInput.disabled = false;
                        tglSelesaiInput.disabled = false;
                        saveBtn.disabled = false;
                        Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    saveBtn.innerText = 'Simpan';
                    input.disabled = false;
                    kroInput.disabled = false;
                    tglMulaiInput.disabled = false;
                    tglSelesaiInput.disabled = false;
                    saveBtn.disabled = false;
                    Swal.fire('Error', 'Gagal menghubungi server', 'error');
                });
        }

        function toggleEditMode(index) {
            const input = document.getElementById('survey-input-' + index);
            const kroInput = document.getElementById('kro-input-' + index);
            const tglMulaiInput = document.getElementById('tgl-mulai-' + index);
            const tglSelesaiInput = document.getElementById('tgl-selesai-' + index);
            const editBtn = document.getElementById('btn-edit-' + index);
            const saveBtn = document.getElementById('btn-save-' + index);
            const displayDiv = document.querySelector('.schedule-display-' + index);
            const inputDiv = document.querySelector('.schedule-input-' + index);
            const textDiv = document.querySelector('.survey-text-' + index);
            const editDiv = document.querySelector('.survey-edit-' + index);

            // Toggle enabled/disabled
            const isCurrentlyDisabled = input.disabled;
            
            input.disabled = !isCurrentlyDisabled;
            kroInput.disabled = !isCurrentlyDisabled;
            tglMulaiInput.disabled = !isCurrentlyDisabled;
            tglSelesaiInput.disabled = !isCurrentlyDisabled;

            if (!isCurrentlyDisabled) {
                // Switching to view mode (disable) - tutup edit panel
                editBtn.style.display = 'block';
                saveBtn.style.display = 'none';
                // show display, hide inputs
                if (displayDiv) displayDiv.classList.remove('hidden');
                if (inputDiv) inputDiv.classList.add('hidden');
                if (textDiv) textDiv.classList.remove('hidden');
                if (editDiv) editDiv.classList.add('hidden');
            } else {
                // Switching to edit mode (enable) - buka edit panel
                editBtn.style.display = 'none';
                saveBtn.style.display = 'block';
                if (displayDiv) displayDiv.classList.add('hidden');
                if (inputDiv) inputDiv.classList.remove('hidden');
                if (textDiv) textDiv.classList.add('hidden');
                if (editDiv) editDiv.classList.remove('hidden');
                input.focus();
            }
        }
        
        function konfirmasiHapus(formId) {
            Swal.fire({
                title: 'Hapus Survei?',
                text: "Nama survei ini akan dihapus dari daftar opsi tim Anda.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // Warna Merah untuk Hapus
                cancelButtonColor: '#3085d6', // Warna Biru untuk Batal
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user klik Ya, baru form di-submit secara manual
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
</body>
