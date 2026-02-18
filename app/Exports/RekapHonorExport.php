<?php

namespace App\Exports;

use App\Models\Placement;
use App\Models\Rate;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class RekapHonorExport implements FromView, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $team_id;
    protected $search;

    public function __construct($bulan, $tahun, $team_id, $search = null)
    {
        $this->tahun = (int) $tahun;
        $this->bulan = !empty($bulan) ? (int) $bulan : null;
        $this->team_id = $team_id;
        $this->search = $search;
    }

    public function view(): View
    {
        $query = Placement::with(['mitra', 'team'])
            ->whereRaw('CAST(year AS UNSIGNED) = ?', [$this->tahun]);

        if ($this->bulan) $query->where('month', $this->bulan);
        if ($this->team_id != 'all' && $this->team_id != null) $query->where('team_id', $this->team_id);
        if ($this->search) {
            $search = $this->search;
            $query->whereHas('mitra', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"));
        }

        $placements = $query->orderBy('mitra_id')->get();
        $rates = Rate::whereRaw('CAST(year AS UNSIGNED) = ?', [$this->tahun])->get();
        $rateMap = [];
        foreach ($rates as $r) {
            $rateMap[$r->survey_name][$r->month] = $r->cost;
        }

        $surveiData = \App\Models\Survei::all();
        $surveyDetailMap = [];
        $surveyDetailMapNormalized = [];

        foreach ($surveiData as $s) {
            Carbon::setLocale('id');
            $jadwalFormatted = '-';
            if ($s->jadwal_kegiatan && $s->jadwal_berakhir_kegiatan) {
                $start = Carbon::parse($s->jadwal_kegiatan);
                $end = Carbon::parse($s->jadwal_berakhir_kegiatan);

                if ($start->month == $end->month && $start->year == $end->year) {
                    $jadwalFormatted =
                        $start->day . '-' . $end->day . ' ' .
                        $start->translatedFormat('F Y');
                } else {
                    $jadwalFormatted =
                        $start->day . ' ' . $start->translatedFormat('F Y') .
                        ' - ' .
                        $end->day . ' ' . $end->translatedFormat('F Y');
                }
            }

            $detail = [
                'kro' => $s->kro ?? '-',
                'jadwal_kegiatan' => $jadwalFormatted,
                'jadwal_berakhir' => '',
            ];

            $surveyDetailMap[$s->nama_survei] = $detail;
            $surveyDetailMapNormalized[strtolower(trim($s->nama_survei))] = $detail;
        }

        $getSurveyDetail = function ($surveyName) use ($surveyDetailMap, $surveyDetailMapNormalized, $surveiData) {
            if (isset($surveyDetailMap[$surveyName])) return $surveyDetailMap[$surveyName];
            $normalized = strtolower(trim($surveyName));
            if (isset($surveyDetailMapNormalized[$normalized])) return $surveyDetailMapNormalized[$normalized];

            foreach ($surveiData as $s) {
                if (stripos($s->nama_survei, $surveyName) !== false || stripos($surveyName, $s->nama_survei) !== false) {
                    Carbon::setLocale('id');
                    $jadwalFormatted = '-';
                    if ($s->jadwal_kegiatan && $s->jadwal_berakhir_kegiatan) {
                        $start = Carbon::parse($s->jadwal_kegiatan);
                        $end = Carbon::parse($s->jadwal_berakhir_kegiatan);
                        $jadwalFormatted = ($start->year == $end->year)
                            ? (($start->month == $end->month)
                                ? $start->day . '-' . $end->day . ' ' . $start->translatedFormat('F Y')
                                : $start->day . ' ' . $start->translatedFormat('F Y') . ' - ' . $end->day . ' ' . $end->translatedFormat('F Y'))
                            : $start->day . ' ' . $start->translatedFormat('F Y') . ' - ' . $end->day . ' ' . $end->translatedFormat('F Y');
                    }
                    return [
                        'kro' => $s->kro ?? '-',
                        'jadwal_kegiatan' => $jadwalFormatted,
                        'jadwal_berakhir' => '',
                    ];
                }
            }

            return ['kro' => '-', 'jadwal_kegiatan' => '-', 'jadwal_berakhir' => '-'];
        };

        $uniqueSurveys = [];
        $surveyTeamMap = [];
        $surveyDetailMapFinal = [];
        $rekapMitra = [];

        foreach ($placements as $p) {
            $teamName = $p->team->name ?? '-';
            foreach (['survey_1', 'survey_2', 'survey_3'] as $sField) {
                if ($p->$sField) {
                    $uniqueSurveys[$p->$sField] = $p->$sField;
                    $surveyTeamMap[$p->$sField] = $teamName;
                    if (!isset($surveyDetailMapFinal[$p->$sField])) {
                        $surveyDetailMapFinal[$p->$sField] = $getSurveyDetail($p->$sField);
                    }
                }
            }

            $mid = $p->mitra_id;
            if (!isset($rekapMitra[$mid])) {
                $rekapMitra[$mid] = [
                    'nama' => $p->mitra->nama_lengkap ?? '-',
                    'sobat_id' => (string) $p->mitra->sobat_id, // PAKSA STRING
                    'kode_kec' => $p->mitra->kode_kec ?? $p->mitra->id_kecamatan ?? '-',
                    'surveys_data' => [],
                    'grand_total' => 0
                ];
            }

            $processSurvey = function ($name, $vol) use (&$rekapMitra, $mid, $rateMap, $p) {
                if (!$name || $vol <= 0) return;
                $harga = $rateMap[$name][$p->month] ?? 0;
                $total = $vol * $harga;
                $rekapMitra[$mid]['grand_total'] += $total;
                if (isset($rekapMitra[$mid]['surveys_data'][$name])) {
                    $rekapMitra[$mid]['surveys_data'][$name]['vol'] += $vol;
                    $rekapMitra[$mid]['surveys_data'][$name]['total'] += $total;
                } else {
                    $rekapMitra[$mid]['surveys_data'][$name] = [
                        'vol' => $vol,
                        'honor_satuan' => $harga,
                        'total' => $total
                    ];
                }
            };
            $processSurvey($p->survey_1, $p->vol_1);
            $processSurvey($p->survey_2, $p->vol_2);
            $processSurvey($p->survey_3, $p->vol_3);
        }

        sort($uniqueSurveys);

        return view('exports.excel_bulanan', [
            'rekapMitra' => $rekapMitra,
            'uniqueSurveys' => $uniqueSurveys,
            'surveyTeamMap' => $surveyTeamMap,
            'surveyDetailMap' => $surveyDetailMapFinal,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun
        ]);
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // Paksa Sobat ID jadi Text
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

                $headerRow = 1;
                $teamBlocks = [];

                // 1️⃣ DETEKSI BLOK TIM
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $cellValue = $sheet->getCell($columnLetter . $headerRow)->getValue();
                    if ($cellValue && str_contains($cellValue, 'Tim:')) {
                        $teamName = trim(str_replace('Tim:', '', $cellValue));
                        $teamBlocks[] = [
                            'colIndex' => $col,
                            'teamName' => $teamName
                        ];
                    }
                }

                // 2️⃣ WARNA BLOK TIM
                $colors = [
                    'E2F0D9',
                    'D9E1F2',
                    'FCE4D6',
                    'FFF2CC',
                    'EAD1DC',
                    'D0E0E3',
                    'F4CCCC',
                    'CFE2F3',
                    'D9D2E9',
                    'F9CB9C',
                    'B6D7A8',
                    'A2C4C9',
                    'FFE599',
                    'B4A7D6',
                    'EA9999',
                    'A4C2F4',
                    'C9DAF8',
                    'F6B26B',
                    'D5A6BD',
                    '76A5AF',
                    '93C47D',
                    'FFD966',
                    '8E7CC3',
                    'E06666',
                    '6FA8DC',
                    'CCCCCC',
                    'D5E8D4',
                    'F8CECC',
                    'DAE8FC',
                    'FCE5CD',
                    'EAD1DC',
                    'CFE2F3',
                    'D9EAD3',
                    'FFF2CC',
                    'F4CCCC'
                ];
                $teamColorMap = [];
                $colorIndex = 0;

                foreach ($teamBlocks as $index => $block) {
                    $startColIndex = $block['colIndex'];
                    $teamName = $block['teamName'];
                    $endColIndex = $highestColumnIndex;
                    if (isset($teamBlocks[$index + 1])) {
                        $endColIndex = $teamBlocks[$index + 1]['colIndex'] - 1;
                    }
                    $startColumn = Coordinate::stringFromColumnIndex($startColIndex);
                    $endColumn = Coordinate::stringFromColumnIndex($endColIndex);
                    $range = $startColumn . '1:' . $endColumn . $highestRow;

                    if (!isset($teamColorMap[$teamName])) {
                        $teamColorMap[$teamName] = $colors[$colorIndex % count($colors)];
                        $colorIndex++;
                    }

                    $sheet->getStyle($range)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $teamColorMap[$teamName]],
                        ],
                    ]);
                }

                // 3️⃣ PAKSA TOTAL BULANAN PUTIH
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $cellValue = $sheet->getCell($columnLetter . $headerRow)->getValue();
                    if ($cellValue && str_contains(strtolower($cellValue), 'total bulanan')) {
                        $range = $columnLetter . '1:' . $columnLetter . $highestRow;
                        $sheet->getStyle($range)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFFFFF'],
                            ],
                        ]);
                    }
                }

                // 4️⃣ PAKSA SOBAT ID JADI STRING MURNI
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getCell('C' . $row)
                        ->setValueExplicit(
                            $sheet->getCell('C' . $row)->getValue(),
                            DataType::TYPE_STRING
                        );
                }
            },
        ];
    }
}
