@php $title = 'Perencanaan Survei Bulanan'; @endphp
@include('mitrabps.headerTemp')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.tailwindcss.com"></script>
@include('mitrabps.cuScroll')

<style>
    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 20;
        background-color: #fff;
        border-right: 2px solid #e5e7eb;
    }

    .sticky-head {
        position: sticky;
        top: 0;
        z-index: 30;
        background-color: #f3f4f6;
    }

    .cell-hover:hover {
        background-color: #f9fafb;
    }
</style>

<body class="bg-gray-100">
    @if (session('success'))
        <script>
            swal("Sukses", "{{ session('success') }}", "success");
        </script>
    @endif
    @if (session('error'))
        <script>
            swal("Gagal", "{{ session('error') }}", "error");
        </script>
    @endif

    <div x-data="{ sidebarOpen: false }" class="flex h-screen">
        <x-sidebar></x-sidebar>
        <div class="flex flex-col flex-1 overflow-hidden">
            <x-navbar></x-navbar>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-4">

                <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                    <h3 class="text-xl font-bold text-gray-800">Matriks Alokasi Kegiatan {{ $year }}</h3>
                    <div class="flex gap-2 items-center flex-wrap justify-end">

                        <form method="GET" class="flex gap-2 items-center">
                            @if (request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            <select name="team_id" onchange="this.form.submit()"
                                class="border rounded px-3 py-2 text-sm font-semibold">
                                <option value="all">-- Semua Tim --</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}"
                                        {{ $selectedTeam == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="year" onchange="this.form.submit()"
                                class="border p-2 rounded text-sm font-bold shadow-sm">
                                @foreach (range(2023, 2030) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endforeach
                            </select>
                        </form>

                        <form method="GET" class="flex gap-2">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="team_id" value="{{ $filterTeamId }}">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari Mitra..." class="border p-2 rounded text-sm w-40 shadow-sm">
                        </form>

                        <a href="{{ route('mitra.penempatan.index') }}"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm flex items-center shadow-sm">Kembali</a>
                    </div>
                </div>

                <div class="bg-white rounded shadow overflow-hidden flex flex-col h-[75vh]">
                    <div class="overflow-auto flex-1">
                        <table class="min-w-max border-collapse w-full">
                            <thead>
                                <tr class="text-gray-600 text-xs uppercase leading-normal">
                                    <th
                                        class="py-2 px-4 text-left border-b sticky-head sticky-col min-w-[200px] z-40 bg-gray-100">
                                        Nama Mitra</th>
                                    @foreach (range(1, 12) as $m)
                                        <th
                                            class="py-2 px-1 text-center border-b border-r sticky-head min-w-[120px] bg-gray-100">
                                            {{ date('M', mktime(0, 0, 0, $m, 1)) }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 text-xs">
                                @foreach ($mitras as $mitra)
                                   @php
                                        $isAdminUser = auth()->user()->role === 'admin' || auth()->user()->is_mitra_admin == 1;
                                    
                                        // pastikan key mitra sesuai dengan placements.mitra_id
                                        $mitraKey = $mitra->id_mitra ?? $mitra->id;
                                    
                                        // daftar tim "Tetap" mitra di tahun ini (dari controller)
                                        $mitraTeamIds = $mitraLocks[$mitraKey] ?? [];
                                    
                                        // mitra dianggap "tim saya" kalau user team_id ada di daftar tim tetap mitra tsb
                                        $isMyTeam = $isAdminUser || ($canEdit && !is_null($userTeamId) && in_array($userTeamId, $mitraTeamIds));
                                    
                                        $rowClass = $isMyTeam ? 'bg-white' : 'bg-gray-50 text-gray-500';
                                    @endphp


                                    <tr class="border-b transition h-16 {{ $rowClass }} hover:bg-gray-100">
                                        <td
                                            class="py-2 px-4 text-left sticky-col shadow-sm border-b {{ $rowClass }}">
                                            <div class="font-bold text-gray-800 text-sm truncate w-[180px]">
                                                {{ $mitra->nama_lengkap }}
                                            </div>
                                            <div class="text-gray-400 text-[10px]">{{ $mitra->sobat_id }}</div>
                                            @if (!$filterTeamId)
                                                <div class="mt-1">
                                                    <span
                                                        class="text-[9px] px-1 py-0.5 rounded border bg-gray-200 text-gray-600">
                                                        {{ $mitra->placements->first()->team->name ?? '-' }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>

                                        @foreach (range(1, 12) as $m)
                                            <td class="p-1 border-r border-b align-top relative text-center h-24">
                                                @php
                                                    $tasks = $mitra->placements->where('month', $m);
                                                    $currentYear = (int) date('Y');
                                                    $currentMonth = (int) date('n');
                                                    $isPast =
                                                        $year < $currentYear ||
                                                        ($year == $currentYear && $m < $currentMonth);
                                                @endphp

                                                @if ($tasks->count() > 0)
                                                    <div
                                                        class="flex flex-col gap-1 h-full overflow-y-auto custom-scrollbar">
                                                        @foreach ($tasks as $task)
                                                            @php
                                                                $bgClass =
                                                                    $task->status_anggota == 'BKO'
                                                                        ? 'bg-yellow-100 text-yellow-800 border-yellow-200'
                                                                        : 'bg-blue-100 text-blue-800 border-blue-200';
                                                            @endphp
                                                            <div
                                                                class="{{ $bgClass }} border rounded p-1 text-left relative group shadow-sm">
                                                                <div class="text-[8px] opacity-75 truncate mb-1">
                                                                    {{ $task->team->name ?? '-' }}</div>

                                                                @php
                                                                    $isAdminUser = auth()->user()->role === 'admin' || auth()->user()->is_mitra_admin == 1;
                                                                    $canManageTask = $isAdminUser || ($canEdit && !is_null($userTeamId) && $task->team_id == $userTeamId);
                                                                    $surveyItems = [
                                                                        ['slot' => 1, 'name' => $task->survey_1, 'vol' => $task->vol_1],
                                                                        ['slot' => 2, 'name' => $task->survey_2, 'vol' => $task->vol_2],
                                                                        ['slot' => 3, 'name' => $task->survey_3, 'vol' => $task->vol_3],
                                                                    ];
                                                                @endphp

                                                                <div class="space-y-1">
                                                                    @foreach ($surveyItems as $item)
                                                                        @if (!empty($item['name']))
                                                                            <div class="bg-white/40 rounded px-1 py-0.5 border border-white/30">
                                                                                <div class="flex items-start justify-between gap-2">
                                                                                    <div class="min-w-0">
                                                                                        <div class="font-bold truncate text-[9px] leading-tight" title="{{ $item['name'] }}">
                                                                                            {{ $item['name'] }}
                                                                                        </div>
                                                                                        <span class="text-[8px] bg-white/50 px-1 rounded border border-black/10 inline-block mt-0.5">v:{{ $item['vol'] }}</span>
                                                                                    </div>

                                                                                    @if ($canManageTask)
                                                                                        <div class="flex items-center gap-1 shrink-0 mt-0.5">
                                                                                            <button
                                                                                                type="button"
                                                                                                onclick='openEditModal(@json($task), "{{ $mitra->nama_lengkap }}", {{ $m }})'
                                                                                                class="inline-flex items-center"
                                                                                                title="Edit survei"
                                                                                                aria-label="Edit survei">
                                                                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" style="color:#2563eb">
                                                                                                    <path d="M16.862 3.487a1.75 1.75 0 0 1 2.475 0l1.176 1.176a1.75 1.75 0 0 1 0 2.475L9.06 18.59a1.75 1.75 0 0 1-.74.435l-4.11 1.173a.75.75 0 0 1-.927-.927l1.173-4.11c.08-.28.23-.533.435-.74L16.862 3.487Zm1.414 1.06-12.3 12.3-.74 2.59 2.59-.74 12.3-12.3-1.85-1.85Z"/>
                                                                                                </svg>
                                                                                            </button>

                                                                                            @if ($item['slot'] === 1)
                                                                                                <form method="POST"
                                                                                                      action="{{ route('placement.destroy', $task->id) }}"
                                                                                                      onsubmit="return confirmDelete(event, this, {{ $isPast ? 'true' : 'false' }})"
                                                                                                      class="inline">
                                                                                                    @csrf
                                                                                                    @method('DELETE')
                                                                                                    <button type="submit" class="inline-flex items-center" title="Hapus semua tugas di bulan ini" aria-label="Hapus semua tugas">
                                                                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" style="color:#dc2626">
                                                                                                            <path d="M9 3.75A2.25 2.25 0 0 1 11.25 1.5h1.5A2.25 2.25 0 0 1 15 3.75V5h4.5a.75.75 0 0 1 0 1.5h-1.1l-1.02 14.02A2.25 2.25 0 0 1 15.14 22.5H8.86a2.25 2.25 0 0 1-2.24-1.98L5.6 6.5H4.5a.75.75 0 0 1 0-1.5H9V3.75Zm1.5 1.25h3V3.75A.75.75 0 0 0 12.75 3h-1.5a.75.75 0 0 0-.75.75V5ZM8.12 6.5l.97 13.4a.75.75 0 0 0 .75.6h6.32a.75.75 0 0 0 .75-.6l.97-13.4H8.12Z"/>
                                                                                                        </svg>
                                                                                                    </button>
                                                                                                </form>
                                                                                            @else
                                                                                                <button
                                                                                                    type="button"
                                                                                                    onclick='deleteSurveySlot(@json($task), {{ $item['slot'] }}, {{ $isPast ? 'true' : 'false' }})'
                                                                                                    class="inline-flex items-center"
                                                                                                    title="Hapus survei ini"
                                                                                                    aria-label="Hapus survei ini">
                                                                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" style="color:#dc2626">
                                                                                                        <path d="M9 3.75A2.25 2.25 0 0 1 11.25 1.5h1.5A2.25 2.25 0 0 1 15 3.75V5h4.5a.75.75 0 0 1 0 1.5h-1.1l-1.02 14.02A2.25 2.25 0 0 1 15.14 22.5H8.86a2.25 2.25 0 0 1-2.24-1.98L5.6 6.5H4.5a.75.75 0 0 1 0-1.5H9V3.75Zm1.5 1.25h3V3.75A.75.75 0 0 0 12.75 3h-1.5a.75.75 0 0 0-.75.75V5ZM8.12 6.5l.97 13.4a.75.75 0 0 0 .75.6h6.32a.75.75 0 0 0 .75-.6l.97-13.4H8.12Z"/>
                                                                                                    </svg>
                                                                                                </button>
                                                                                            @endif
                                                                                        </div>
                                                                                    @elseif(!$canManageTask)
                                                                                        <i class="bi bi-lock-fill text-[8px] text-gray-400"></i>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach

                                                        {{--@if (!$isPast && $isMyTeam)
                                                            <button
                                                                onclick="openAddModal({{ $mitra->id_mitra ?? $mitra->id }}, '{{ $mitra->nama_lengkap }}', {{ $m }}, null)"
                                                                class="text-[8px] text-gray-400 hover:text-green-600 w-full text-center border border-dashed border-gray-300 rounded hover:bg-green-50 transition">+
                                                                Add</button>
                                                        @endif--}}
                                                    </div>
                                                @else
                                                    @if ($isPast)
                                                        <div
                                                            class="h-full flex items-center justify-center text-gray-300 cursor-not-allowed">
                                                            <span class="text-xs">-</span>
                                                        </div>
                                                    @else
                                                        <div class="h-full flex items-center justify-center">
                                                            @if ($isMyTeam)
                                                                <button
                                                                    onclick="openAddModal({{ $mitra->id_mitra ?? $mitra->id }}, '{{ $mitra->nama_lengkap }}', {{ $m }}, null)"
                                                                    class="text-green-500 hover:text-green-700 hover:bg-green-100 p-2 rounded-full transition transform hover:scale-110"
                                                                    title="Tambah Tugas">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="h-5 w-5" fill="none"
                                                                        viewBox="0 0 24 24" stroke="currentColor"
                                                                        stroke-width="2">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M12 4v16m8-8H4" />
                                                                    </svg>
                                                                </button>
                                                            @else
                                                                <span class="text-gray-300 cursor-not-allowed"
                                                                    title="Terkunci: Bukan Tim Anda"><i
                                                                        class="bi bi-lock-fill text-lg"></i></span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
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
            </main>
        </div>
    </div>

    <div id="assignment-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeAssignmentModal()">
            </div>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="assignment-form" method="POST">
                    @csrf
                    <div id="method-spoofing"></div>

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Input Penugasan
                            </h3>
                            <button type="button" onclick="closeAssignmentModal()"
                                class="text-gray-400 hover:text-gray-500 text-2xl">&times;</button>
                        </div>

                        <input type="hidden" name="mitra_id" id="modal_mitra_id">
                        <input type="hidden" name="year" id="modal_year" value="{{ $year }}">
                        <input type="hidden" name="month" id="modal_month">
                        <input type="hidden" id="modal_is_past" value="0">

                        <div class="mb-4 p-3 bg-blue-50 rounded border border-blue-100 text-sm">
                            <p><strong>Mitra:</strong> <span id="display_mitra_name" class="font-bold"></span></p>
                            <p><strong>Periode:</strong> <span id="display_month" class="font-bold"></span>
                                {{ $year }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Tim Tujuan</label>
                            <select name="team_id" id="modal_team_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm p-2 border"
                                required onchange="updateModalSurveys()">
                                <option value="">-- Pilih Tim --</option>
                                @foreach ($teams as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <small id="lock-msg" class="text-red-500 text-xs hidden mt-1"><i
                                    class="bi bi-lock-fill"></i> Mitra ini terkunci di tim ini.</small>
                        </div>

                        <div class="grid grid-cols-4 gap-4 mb-3">
                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Survei 1 *</label>
                                <select name="survey_1" id="survey_1" onchange="updateSurveyOptions()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 border">
                                    <option value="">-- Pilih Tim Dulu --</option>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Vol</label>
                                <input type="number" name="vol_1" id="vol_1" value="1" min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-center">
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-4 mb-3">
                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Survei 2 (Tambahan)</label>
                                <select name="survey_2" id="survey_2" onchange="updateSurveyOptions(); autoSetVolume(2)"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 border">
                                    <option value="">-- Kosong --</option>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Vol</label>
                                <input type="number" name="vol_2" id="vol_2" value="1" min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-center">
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-4 mb-3">
                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Survei 3 (Tambahan)</label>
                                <select name="survey_3" id="survey_3" onchange="updateSurveyOptions(); autoSetVolume(3)"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 border">
                                    <option value="">-- Kosong --</option>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Vol</label>
                                <input type="number" name="vol_3" id="vol_3" value="1" min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-center">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="force_save" id="force_save" value="0">

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                        <button type="button" onclick="closeAssignmentModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const monthNames = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September",
            "Oktober", "November", "Desember"
        ];
        const teamSurveys = @json($teamSurveys);
        const planningYear = {{ (int) $year }};

        function isHistoricalMonth(month) {
            const now = new Date();
            const currentYear = now.getFullYear();
            const currentMonth = now.getMonth() + 1;
            return planningYear < currentYear || (planningYear === currentYear && month < currentMonth);
        }

        function openAddModal(mitraId, mitraName, month, lockedTeamId) {
            resetModal();
            document.getElementById('force_save').value = '0';
            document.getElementById('modal_is_past').value = isHistoricalMonth(month) ? '1' : '0';

            document.getElementById('modal-title').innerText = "Input Penugasan Baru";
            document.getElementById('display_mitra_name').textContent = mitraName;
            document.getElementById('display_month').textContent = monthNames[month];

            document.getElementById('modal_mitra_id').value = mitraId;
            document.getElementById('modal_month').value = month;

            const form = document.getElementById('assignment-form');
            form.action = "{{ route('placement.store') }}";
            form.dataset.url = "{{ route('placement.store') }}"; // URL AJAX

            handleTeamLocking(lockedTeamId);
            document.getElementById('assignment-modal').classList.remove('hidden');

            setTimeout(() => {
                updateSurveyOptions();
            }, 100);
        }

        function openEditModal(taskData, mitraName, month) {
            resetModal();
            document.getElementById('force_save').value = '0';
            document.getElementById('modal_is_past').value = isHistoricalMonth(month) ? '1' : '0';

            document.getElementById('modal-title').innerText = "Edit / Tambah Tugas";
            document.getElementById('display_mitra_name').textContent = mitraName;
            document.getElementById('display_month').textContent = monthNames[month];

            document.getElementById('modal_mitra_id').value = taskData.mitra_id;
            document.getElementById('modal_month').value = month;

            // Handle Team Select
            const teamSelect = document.getElementById('modal_team_id');
            teamSelect.value = taskData.team_id;
            teamSelect.disabled = true;
            teamSelect.classList.add('bg-gray-100');

            // Hidden Team Input (Fix disabled not sending)
            let hiddenTeam = document.getElementById('hidden-team-id-fix');
            if (!hiddenTeam) {
                hiddenTeam = document.createElement('input');
                hiddenTeam.type = 'hidden';
                hiddenTeam.name = 'team_id';
                hiddenTeam.id = 'hidden-team-id-fix';
                document.querySelector('#assignment-form').appendChild(hiddenTeam);
            }
            hiddenTeam.value = taskData.team_id;

            updateModalSurveys();

            setTimeout(() => {
                if (taskData.survey_1) document.getElementById('survey_1').value = taskData.survey_1;
                if (taskData.survey_2) document.getElementById('survey_2').value = taskData.survey_2;
                if (taskData.survey_3) document.getElementById('survey_3').value = taskData.survey_3;
                document.getElementById('vol_1').value = taskData.vol_1 || 1;
                // Set volume ke 1 jika ada survei, ke 0 jika tidak
                document.getElementById('vol_2').value = taskData.survey_2 ? (taskData.vol_2 || 1) : 0;
                document.getElementById('vol_3').value = taskData.survey_3 ? (taskData.vol_3 || 1) : 0;
                updateSurveyOptions();
            }, 100);

            const form = document.getElementById('assignment-form');
            const updateUrl = "{{ route('placement.update', ':id') }}".replace(':id', taskData.id);
            form.action = updateUrl;
            form.dataset.url = updateUrl; // URL AJAX

            document.getElementById('method-spoofing').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('assignment-modal').classList.remove('hidden');
        }

        function handleTeamLocking(lockedTeamId) {
            const teamSelect = document.getElementById('modal_team_id');
            const lockMsg = document.getElementById('lock-msg');
            let hiddenInput = document.getElementById('hidden-team-id-fix');
            if (hiddenInput) hiddenInput.remove();

            if (lockedTeamId) {
                teamSelect.value = lockedTeamId;
                teamSelect.disabled = true;
                teamSelect.classList.add('bg-gray-100');
                lockMsg.classList.remove('hidden');

                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'team_id';
                hiddenInput.id = 'hidden-team-id-fix';
                hiddenInput.value = lockedTeamId;
                document.querySelector('#assignment-form').appendChild(hiddenInput);
                updateModalSurveys();
            } else {
                teamSelect.value = "";
                teamSelect.disabled = false;
                teamSelect.classList.remove('bg-gray-100');
                lockMsg.classList.add('hidden');
                document.getElementById('survey_1').innerHTML = '<option value="">-- Pilih Tim Dulu --</option>';
            }
        }

        function updateModalSurveys() {
            const teamId = document.getElementById('modal_team_id').value;
            const selects = ['survey_1', 'survey_2', 'survey_3'];
            selects.forEach(id => {
                const el = document.getElementById(id);
                el.innerHTML = (id === 'survey_1') ? '<option value="">-- Pilih Survei --</option>' :
                    '<option value="">-- Kosong --</option>';
            });

            if (teamId && teamSurveys[teamId]) {
                const surveys = teamSurveys[teamId];
                const list = Array.isArray(surveys) ? surveys : Object.keys(surveys);
                list.forEach(s => {
                    selects.forEach(id => {
                        let opt = document.createElement('option');
                        // Handle both old (string) and new (array) format
                        if (typeof s === 'string') {
                            opt.value = s;
                            opt.innerHTML = s;
                        } else if (s && s.name) {
                            opt.value = s.name;
                            opt.innerHTML = s.name + (s.kro ? ' [' + s.kro + ']' : '');
                        }
                        document.getElementById(id).appendChild(opt);
                    });
                });
            }
            updateSurveyOptions();
        }

        function resetModal() {
            document.getElementById('assignment-form').reset();
            document.getElementById('method-spoofing').innerHTML = '';
            document.getElementById('lock-msg').classList.add('hidden');
            const forceSaveInput = document.getElementById('force_save');
            if (forceSaveInput) forceSaveInput.value = '0';

            const teamSelect = document.getElementById('modal_team_id');
            teamSelect.disabled = false;
            teamSelect.classList.remove('bg-gray-100');

            let hiddenTeam = document.getElementById('hidden-team-id-fix');
            if (hiddenTeam) hiddenTeam.remove();

            ['survey_1', 'survey_2', 'survey_3'].forEach(id => {
                const el = document.getElementById(id);
                if (el) Array.from(el.options).forEach(opt => opt.style.display = 'block');
            });
        }

        function closeAssignmentModal() {
            document.getElementById('assignment-modal').classList.add('hidden');
        }

        function confirmDelete(event, form, isPast = false) {
            event.preventDefault();

            const runDeleteConfirm = () => {
                Swal.fire({
                    title: 'Hapus penugasan?',
                    text: 'Semua survei pada penugasan bulan ini akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            };

            if (isPast) {
                confirmHistoricalAction('hapus data').then((ok) => {
                    if (ok) runDeleteConfirm();
                });
            } else {
                runDeleteConfirm();
            }

            return false;
        }

        function confirmHistoricalAction(actionLabel) {
            return Swal.fire({
                title: 'Perubahan Data Histori',
                html: `Anda akan <b>${actionLabel}</b> untuk bulan lampau.<br>Lanjutkan?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then(result => result.isConfirmed);
        }

        function deleteSurveySlot(taskData, slot, isPast = false) {
            const runDelete = () => Swal.fire({
                title: 'Hapus survei ini?',
                text: 'Survei terpilih akan dikosongkan dari penugasan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const payload = new FormData();
                payload.append('_method', 'PUT');
                payload.append('team_id', taskData.team_id);
                payload.append('survey_1', taskData.survey_1 || '');
                payload.append('vol_1', taskData.vol_1 || 1);
                payload.append('survey_2', taskData.survey_2 || '');
                payload.append('vol_2', taskData.vol_2 || 1);
                payload.append('survey_3', taskData.survey_3 || '');
                payload.append('vol_3', taskData.vol_3 || 1);

                if (slot === 2) {
                    payload.set('survey_2', '');
                    payload.set('vol_2', '0');
                }
                if (slot === 3) {
                    payload.set('survey_3', '');
                    payload.set('vol_3', '0');
                }

                const updateUrl = "{{ route('placement.update', ':id') }}".replace(':id', taskData.id);

                fetch(updateUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: payload
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil',
                            text: 'Survei berhasil dihapus.',
                            icon: 'success',
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal', data.message || 'Gagal menghapus survei.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Gagal menghubungi server.', 'error'));
            });

            if (isPast) {
                confirmHistoricalAction('hapus survei').then((ok) => {
                    if (ok) runDelete();
                });
            } else {
                runDelete();
            }
        }

        function updateSurveyOptions() {
            const s1 = document.getElementById('survey_1');
            const s2 = document.getElementById('survey_2');
            const s3 = document.getElementById('survey_3');
            if (!s1 || !s2 || !s3) return;

            const v1 = s1.value;
            const v2 = s2.value;
            const v3 = s3.value;
            const filter = (selectEl, usedValues) => {
                Array.from(selectEl.options).forEach(opt => {
                    if (opt.value === "") return;
                    if (usedValues.includes(opt.value) && opt.value !== selectEl.value) {
                        opt.style.display = 'none';
                    } else {
                        opt.style.display = 'block';
                    }
                });
            };
            filter(s1, [v2, v3]);
            filter(s2, [v1, v3]);
            filter(s3, [v1, v2]);
        }

        // Auto set volume saat survey dipilih/dikosongkan
        function autoSetVolume(index) {
            const surveySelect = document.getElementById(`survey_${index}`);
            const volInput = document.getElementById(`vol_${index}`);
            
            if (surveySelect && volInput) {
                // Jika survei dipilih (tidak kosong) dan volume masih 0, set ke 1
                if (surveySelect.value && volInput.value == 0) {
                    volInput.value = 1;
                }
                // Jika survei dikosongkan, set volume ke 0
                if (!surveySelect.value) {
                    volInput.value = 0;
                }
            }
        }

        // --- AJAX SUBMIT HANDLER ---
        document.addEventListener("DOMContentLoaded", function() {
            // Auto-open edit modal jika ada parameter edit_placement
            @if(isset($editPlacement) && $editPlacement)
                const editData = {
                    id: {{ $editPlacement->id }},
                    mitra_id: {{ $editPlacement->mitra_id }},
                    team_id: {{ $editPlacement->team_id }},
                    survey_1: "{{ $editPlacement->survey_1 }}",
                    vol_1: {{ $editPlacement->vol_1 }},
                    survey_2: "{{ $editPlacement->survey_2 ?? '' }}",
                    vol_2: {{ $editPlacement->vol_2 ?? 0 }},
                    survey_3: "{{ $editPlacement->survey_3 ?? '' }}",
                    vol_3: {{ $editPlacement->vol_3 ?? 0 }}
                };
                openEditModal(editData, "{{ $editPlacement->mitra->nama_lengkap }}", {{ $editPlacement->month }});
            @endif
            
            const form = document.getElementById('assignment-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const url = this.dataset.url || this.action;
                                        const isPast = document.getElementById('modal_is_past').value === '1';
                                        const isUpdate = document.querySelector('input[name="_method"][value="PUT"]') !== null;

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerText;
                    submitBtn.innerText = 'Menyimpan...';
                    submitBtn.disabled = true;

                                        const executeSubmit = () => {
                                        fetch(url, {
                                                            method: 'POST',
                                                            credentials: 'same-origin',
                                                            headers: {
                                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                                'Accept': 'application/json',
                                                                'X-Requested-With': 'XMLHttpRequest',
                                                            },
                                                            body: formData
                                                        })
                                                        .then(async (response) => {
                                                            let data;
                            
                                                            // coba JSON dulu
                                                            try {
                                                                data = await response.clone().json();
                                                            } catch (e) {
                                                                // fallback: bukan JSON (HTML/redirect/atau JSON invalid)
                                                                const text = await response.text();
                                                                console.log('Non-JSON response:', text);
                            
                                                                data = {
                                                                    status: response.ok ? 'success' : 'error',
                                                                    message: response.ok ? 'Berhasil disimpan!' : 'Gagal menyimpan (response bukan JSON).'
                                                                };
                                                            }
                            
                                                            // kalau HTTP status error tapi sempat jadi JSON
                                                            if (!response.ok && data?.status !== 'warning') {
                                                                data = { status: 'error', message: data?.message || 'Terjadi kesalahan server' };
                                                            }
                            
                                                            return data;
                                                        })
                                                        .then((data) => {
                                                            submitBtn.innerText = originalText;
                                                            submitBtn.disabled = false;
                            
                                                            if (data.status === 'success') {
                                                                closeAssignmentModal();
                                                                Swal.fire({
                                                                    title: 'Berhasil!',
                                                                    text: data.message,
                                                                    icon: 'success',
                                                                    timer: 1500,
                                                                    showConfirmButton: false
                                                                }).then(() => window.location.reload());
                                                            } else if (data.status === 'warning') {
                                                                Swal.fire({
                                                                    title: 'Peringatan Batas Honor',
                                                                    text: data.message,
                                                                    icon: 'warning',
                                                                    showCancelButton: true,
                                                                    confirmButtonText: 'Ya, Simpan Saja!',
                                                                    cancelButtonText: 'Batal'
                                                                }).then((result) => {
                                                                    if (result.isConfirmed) {
                                                                        document.getElementById('force_save').value = '1';
                                                                        form.dispatchEvent(new Event('submit'));
                                                                    }
                                                                });
                                                            } else {
                                                                Swal.fire({
                                                                    title: 'Gagal!',
                                                                    text: data.message || 'Terjadi kesalahan sistem',
                                                                    icon: 'error'
                                                                });
                                                            }
                                                        })
                                                        .catch((error) => {
                                                            console.error('Fetch Error:', error);
                                                            submitBtn.innerText = originalText;
                                                            submitBtn.disabled = false;
                                                            alert('Gagal menghubungi server. (Cek Console F12 untuk detail)');
                                                        });
                                        };

                                        if (isPast && isUpdate) {
                                                confirmHistoricalAction('ubah data').then((ok) => {
                                                        if (!ok) {
                                                                submitBtn.innerText = originalText;
                                                                submitBtn.disabled = false;
                                                                return;
                                                        }
                                                        executeSubmit();
                                                });
                                                return;
                                        }

                                        executeSubmit();

                });
            }
        });
    </script>
</body>
