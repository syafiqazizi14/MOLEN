<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapHonorExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        $sheets = [
            new RekapHonorDetailSheet($this->bulan, $this->tahun, $this->team_id, $this->search),
        ];

        if (!$this->bulan) {
            $sheets[] = new RekapHonorBulananSheet($this->tahun, $this->team_id, $this->search);
            $sheets[] = new RekapHonorTahunanSheet($this->tahun, $this->team_id, $this->search);
        }

        return $sheets;
    }
}
