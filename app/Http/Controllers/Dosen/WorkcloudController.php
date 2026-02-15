<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Evaluasi;
use App\Models\Mk;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WorkcloudController extends Controller
{
    function __construct()
    {
        // $this->middleware('permission:read workcloud-mks', ['only' => ['index','show']]);
    }

    public function index(Mk $mk)
    {
        return view('obe.report.workcloud-per-mk', $this->buildNilaiPageData($mk));
    }

    public function exportKelas(Mk $mk, Request $request)
    {
        $kelas = trim((string) $request->query('kelas', ''));
        if ($kelas === '') {
            return back()->with('error', 'Parameter kelas tidak valid untuk export.');
        }

        $semesterId = $request->query('semester_id');
        $data = $this->buildNilaiPageData($mk);

        $kontrakMks = collect($data['kontrakMks']);
        $workclouds = collect($data['workclouds']);
        $workcloudMetas = collect($data['workcloudMetas']);
        $avgByWorkcloud = $data['avgByWorkcloud'];
        $semesters = collect($data['semesters']);

        $kelasRows = $kontrakMks->filter(function ($item) use ($kelas) {
            $itemKelas = trim((string) ($item->kelas ?? ''));
            $normalized = $itemKelas !== '' ? $itemKelas : 'Tanpa Kelas';
            return $normalized === $kelas;
        })->values();

        if ($semesterId !== null && $semesterId !== '') {
            $kelasRows = $kelasRows->where('semester_id', (int) $semesterId)->values();
        }

        $workcloudColumnsCount = max(1, $workcloudMetas->count());
        $lastColumnIndex = 4 + $workcloudColumnsCount;
        $lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(Str::limit('Kelas ' . $kelas, 31, ''));

        $sheet->mergeCells('A1:A3');
        $sheet->mergeCells('B1:B3');
        $sheet->mergeCells('C1:D1');
        $sheet->mergeCells('E1:' . $lastColumn . '1');
        $sheet->mergeCells('C2:C3');
        $sheet->mergeCells('D2:D3');

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Mahasiswa');
        $sheet->setCellValue('C1', 'Nilai Akhir');
        $sheet->setCellValue('E1', 'Nilai Komponen Evaluasi');
        $sheet->setCellValue('C2', 'Nilai');
        $sheet->setCellValue('D2', 'Grade');

        if ($workcloudMetas->isNotEmpty()) {
            $columnIndex = 5;
            foreach ($workcloudMetas as $meta) {
                $column = Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue(
                    $column . '2',
                    $meta['name'] . "\n(" . number_format((float) $meta['bobot'], 2) . '%)'
                );
                $cplText = !empty($meta['cpls']) ? implode(', ', $meta['cpls']) : '-';
                $sheet->setCellValue($column . '3', 'CPL: ' . $cplText);
                $columnIndex++;
            }
        } else {
            $sheet->setCellValue('E2', 'Belum ada kategori workcloud');
            $sheet->setCellValue('E3', 'CPL: -');
        }

        $currentRow = 4;
        if ($kelasRows->isNotEmpty()) {
            foreach ($kelasRows as $index => $kontrakMk) {
                $sheet->setCellValue('A' . $currentRow, $index + 1);
                $sheet->setCellValue(
                    'B' . $currentRow,
                    (string) ($kontrakMk->mahasiswa->nim ?? '') . "\n" . (string) ($kontrakMk->mahasiswa->nama ?? '')
                );

                $nilaiAngka = $kontrakMk->nilai_angka;
                if ($nilaiAngka !== null) {
                    $sheet->setCellValue('C' . $currentRow, round((float) $nilaiAngka, 2));
                } else {
                    $sheet->setCellValue('C' . $currentRow, '-');
                }
                $sheet->setCellValue('D' . $currentRow, $kontrakMk->nilai_huruf ?? '-');

                if ($workclouds->isNotEmpty()) {
                    $columnIndex = 5;
                    foreach ($workclouds as $workcloud) {
                        $column = Coordinate::stringFromColumnIndex($columnIndex);
                        $key = $kontrakMk->mahasiswa_id . '_' . $kontrakMk->semester_id . '_' . $workcloud;
                        $avgObj = $avgByWorkcloud[$key] ?? null;
                        $sheet->setCellValue($column . $currentRow, $avgObj ? round((float) $avgObj->avg_nilai, 2) : '-');
                        $columnIndex++;
                    }
                } else {
                    $sheet->setCellValue('E' . $currentRow, '-');
                }

                $currentRow++;
            }

            $kelasAvgNilaiAngka = $kelasRows->whereNotNull('nilai_angka')->average('nilai_angka');
            $sheet->mergeCells('A' . $currentRow . ':B' . $currentRow);
            $sheet->setCellValue('A' . $currentRow, 'RATA-RATA KELAS');
            $sheet->setCellValue('C' . $currentRow, $kelasAvgNilaiAngka !== null ? round((float) $kelasAvgNilaiAngka, 2) : '-');
            $sheet->setCellValue('D' . $currentRow, '-');

            if ($workclouds->isNotEmpty()) {
                $columnIndex = 5;
                foreach ($workclouds as $workcloud) {
                    $kelasWorkcloudValues = $kelasRows->map(function ($row) use ($workcloud, $avgByWorkcloud) {
                        $rowKey = $row->mahasiswa_id . '_' . $row->semester_id . '_' . $workcloud;
                        return isset($avgByWorkcloud[$rowKey]) ? (float) $avgByWorkcloud[$rowKey]->avg_nilai : null;
                    })->filter(function ($item) {
                        return $item !== null;
                    });

                    $kelasWorkcloudAvg = $kelasWorkcloudValues->count() > 0 ? $kelasWorkcloudValues->average() : null;
                    $column = Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->setCellValue($column . $currentRow, $kelasWorkcloudAvg !== null ? round((float) $kelasWorkcloudAvg, 2) : '-');
                    $columnIndex++;
                }
            } else {
                $sheet->setCellValue('E' . $currentRow, '-');
            }
        } else {
            $sheet->mergeCells('A4:' . $lastColumn . '4');
            $sheet->setCellValue('A4', 'Tidak ada data mahasiswa pada filter yang dipilih.');
            $currentRow = 4;
        }

        $sheet->getStyle('A1:' . $lastColumn . '3')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastColumn . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:' . $lastColumn . '3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B1:B' . $currentRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('E2:' . $lastColumn . '3')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:' . $lastColumn . $currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(38);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(10);
        for ($columnIndex = 5; $columnIndex <= $lastColumnIndex; $columnIndex++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setWidth(16);
        }

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(30);
        $sheet->getRowDimension(3)->setRowHeight(26);

        $safeKodeMk = Str::slug((string) ($mk->kode ?? 'mk'), '-');
        $safeKelas = Str::slug($kelas, '-');
        $semesterLabel = '';
        if ($semesterId !== null && $semesterId !== '') {
            $semester = $semesters->firstWhere('id', (int) $semesterId);
            if ($semester) {
                $semesterLabel = '-' . Str::slug((string) $semester->kode, '-');
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'rekap-workcloud-' . $safeKodeMk . '-kelas-' . ($safeKelas !== '' ? $safeKelas : 'tanpa-kelas') . $semesterLabel . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildNilaiPageData(Mk $mk): array
    {
        $semesters = $mk->kontrakMks()
            ->whereNotNull('semester_id')
            ->with('semester')
            ->get()
            ->pluck('semester')
            ->filter()
            ->unique('id')
            ->sortByDesc('status_aktif')
            ->sortByDesc('kode')
            ->values();

        $penugasans = $mk->penugasans()->orderBy('kode')->get();

        $kontrakMks = $mk->kontrakMks()
            ->with(['mahasiswa', 'semester'])
            ->whereNotNull('mahasiswa_id')
            ->whereNotNull('semester_id')
            ->get()
            ->filter(fn ($kontrakMk) => $kontrakMk->mahasiswa !== null)
            ->sortBy(fn ($kontrakMk) => Str::lower((string) ($kontrakMk->mahasiswa->nim ?? '')))
            ->values();

        $mahasiswaIds = $kontrakMks->pluck('mahasiswa_id')->filter()->unique()->values();
        $semesterIds = $kontrakMks->pluck('semester_id')->filter()->unique()->values();

        $workclouds = Evaluasi::query()
            ->whereNotNull('workcloud')
            ->where('workcloud', '!=', '')
            ->select('workcloud')
            ->distinct()
            ->orderBy('workcloud')
            ->pluck('workcloud')
            ->values();

        $bobotByWorkcloud = $mk->penugasans()
            ->join('evaluasis', 'penugasans.evaluasi_id', '=', 'evaluasis.id')
            ->whereNotNull('evaluasis.workcloud')
            ->selectRaw('evaluasis.workcloud, COALESCE(SUM(penugasans.bobot),0) as total_bobot')
            ->groupBy('evaluasis.workcloud')
            ->pluck('total_bobot', 'workcloud')
            ->all();

        $cplsByWorkcloud = DB::table('penugasans')
            ->join('evaluasis', 'penugasans.evaluasi_id', '=', 'evaluasis.id')
            ->leftJoin('join_subcpmk_penugasans', function ($join) use ($mk) {
                $join->on('join_subcpmk_penugasans.penugasan_id', '=', 'penugasans.id')
                    ->where('join_subcpmk_penugasans.mk_id', '=', $mk->id);
            })
            ->leftJoin('subcpmks', 'subcpmks.id', '=', 'join_subcpmk_penugasans.subcpmk_id')
            ->leftJoin('join_cpl_cpmks', 'join_cpl_cpmks.id', '=', 'subcpmks.join_cpl_cpmk_id')
            ->leftJoin('join_cpl_bks', 'join_cpl_bks.id', '=', 'join_cpl_cpmks.join_cpl_bk_id')
            ->leftJoin('cpls', 'cpls.id', '=', 'join_cpl_bks.cpl_id')
            ->where('penugasans.mk_id', $mk->id)
            ->whereNotNull('evaluasis.workcloud')
            ->select('evaluasis.workcloud', 'cpls.kode')
            ->get()
            ->groupBy('workcloud')
            ->map(function ($items) {
                return $items
                    ->pluck('kode')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();
            })
            ->all();

        $workcloudMetas = collect($workclouds)
            ->map(function ($workcloud) use ($bobotByWorkcloud, $cplsByWorkcloud) {
                return [
                    'name' => $workcloud,
                    'bobot' => (float) ($bobotByWorkcloud[$workcloud] ?? 0),
                    'cpls' => $cplsByWorkcloud[$workcloud] ?? [],
                ];
            })
            ->values();

        $avgByWorkcloud = Nilai::query()
            ->join('penugasans', 'nilais.penugasan_id', '=', 'penugasans.id')
            ->join('evaluasis', 'penugasans.evaluasi_id', '=', 'evaluasis.id')
            ->where('nilais.mk_id', $mk->id)
            ->whereIn('nilais.mahasiswa_id', $mahasiswaIds)
            ->whereIn('nilais.semester_id', $semesterIds)
            ->whereNotNull('evaluasis.workcloud')
            ->selectRaw('nilais.mahasiswa_id, nilais.semester_id, evaluasis.workcloud, AVG(nilais.nilai) as avg_nilai')
            ->groupBy('nilais.mahasiswa_id', 'nilais.semester_id', 'evaluasis.workcloud')
            ->get()
            ->keyBy(function ($item) {
                return $item->mahasiswa_id . '_' . $item->semester_id . '_' . $item->workcloud;
            })
            ->all();

        $classAvgByWorkcloud = Nilai::query()
            ->join('penugasans', 'nilais.penugasan_id', '=', 'penugasans.id')
            ->join('evaluasis', 'penugasans.evaluasi_id', '=', 'evaluasis.id')
            ->where('nilais.mk_id', $mk->id)
            ->whereIn('nilais.semester_id', $semesterIds)
            ->whereNotNull('evaluasis.workcloud')
            ->selectRaw('evaluasis.workcloud, AVG(nilais.nilai) as avg_nilai')
            ->groupBy('evaluasis.workcloud')
            ->pluck('avg_nilai', 'workcloud')
            ->all();

        return compact('mk', 'semesters', 'penugasans', 'kontrakMks', 'workclouds', 'workcloudMetas', 'avgByWorkcloud', 'classAvgByWorkcloud');
    }
}
