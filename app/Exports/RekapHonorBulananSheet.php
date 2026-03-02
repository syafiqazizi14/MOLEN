<?php

namespace App\Exports;

use App\Models\Placement;
use App\Models\Rate;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class RekapHonorBulananSheet implements FromArray, WithHeadings, WithTitle, WithColumnFormatting, ShouldAutoSize, WithEvents
{
    protected $tahun;
    protected $team_id;
    protected $search;

    public function __construct($tahun, $team_id, $search = null)
    {
        $this->tahun = (int) $tahun;
        $this->team_id = $team_id;
        $this->search = $search;
    }

    public function title(): string
    {
        return 'Rekap Bulanan';
    }

    public function headings(): array
    {
        return ['No', 'Nama Mitra', 'SOBAT ID', 'Tim', 'Bulan', 'Total'];
    }

    public function array(): array
    {
        $query = Placement::with(['mitra', 'team'])
            ->whereRaw('CAST(year AS UNSIGNED) = ?', [$this->tahun]);

        if ($this->team_id != 'all' && $this->team_id != null) {
            $query->where('team_id', $this->team_id);
        }
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

        $mitraMap = [];

        foreach ($placements as $p) {
            $mid = $p->mitra_id;
            if (!isset($mitraMap[$mid])) {
                $mitraMap[$mid] = [
                    'nama' => $p->mitra->nama_lengkap ?? '-',
                    'sobat_id' => (string) ($p->mitra->sobat_id ?? '-'),
                    'teams' => [],
                    'monthly_totals' => array_fill(1, 12, 0),
                ];
            }

            $teamName = $p->team->name ?? '-';
            $mitraMap[$mid]['teams'][$teamName] = true;

            $processSurvey = function ($name, $vol) use (&$mitraMap, $mid, $rateMap, $p) {
                if (!$name || $vol <= 0) {
                    return;
                }
                $harga = $rateMap[$name][$p->month] ?? 0;
                $total = $vol * $harga;
                $mitraMap[$mid]['monthly_totals'][$p->month] += $total;
            };

            $processSurvey($p->survey_1, $p->vol_1);
            $processSurvey($p->survey_2, $p->vol_2);
            $processSurvey($p->survey_3, $p->vol_3);
        }

        $rows = [];
        $no = 1;
        Carbon::setLocale('id');

        foreach ($mitraMap as $mitraData) {
            $teamLabel = implode(', ', array_keys($mitraData['teams'])) ?: '-';
            for ($month = 1; $month <= 12; $month++) {
                $monthTotal = $mitraData['monthly_totals'][$month] ?? 0;
                if ($monthTotal <= 0) {
                    continue;
                }
                $monthName = Carbon::create($this->tahun, $month, 1)->translatedFormat('F');
                $rows[] = [
                    $no,
                    $mitraData['nama'],
                    $mitraData['sobat_id'],
                    $teamLabel,
                    $monthName,
                    $monthTotal,
                ];
                $no++;
            }
        }

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $headerRange = 'A1:' . $highestColumn . '1';
                $dataRange = 'A1:' . $highestColumn . $highestRow;

                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');

                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                if ($highestRow >= 2) {
                    $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('C2:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E2:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('F2:F' . $highestRow)->getNumberFormat()->setFormatCode('"Rp" #,##0');

                    for ($row = 2; $row <= $highestRow; $row++) {
                        $value = $sheet->getCell('C' . $row)->getValue();
                        if ($value !== null && $value !== '') {
                            $sheet->getCell('C' . $row)->setValueExplicit((string) $value, DataType::TYPE_STRING);
                        }
                    }
                }
            },
        ];
    }
}
