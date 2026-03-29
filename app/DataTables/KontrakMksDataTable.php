<?php

namespace App\DataTables;

use App\Models\KontrakMk;
use App\Models\JoinProdiUser;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Services\DataTable;

class KontrakMksDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function($row){
                $prodiId = (string) ($row->mahasiswa->prodi_id ?? '');
                $mahasiswaId = (string) ($row->mahasiswa_id ?? '');
                $mkId = (string) ($row->mk_id ?? '');
                $userId = (string) ($row->user_id ?? '');
                $semesterId = (string) ($row->semester_id ?? '');
                $kelas = e((string) ($row->kelas ?? ''));
                $action = '<div class="row">';
                $action .= ' <div class="col-auto">';
                $action .= '  <button type="button" class="btn btn-primary btn-sm action js-kontrakmk-modal-trigger" data-bs-toggle="modal" data-bs-target="#modalEditKontrakmk" data-kontrakmk-id="'.$row->id.'" data-kontrakmk-prodi-id="'.$prodiId.'" data-kontrakmk-mahasiswa-id="'.$mahasiswaId.'" data-kontrakmk-mk-id="'.$mkId.'" data-kontrakmk-user-id="'.$userId.'" data-kontrakmk-semester-id="'.$semesterId.'" data-kontrakmk-kelas="'.$kelas.'" title="Edit KontrakMk"><i class="bi bi-pencil-square"></i></button>';
                $action .= ' </div>';
                $action .= '</div>';
                return $action;
            })
            ->addColumn('prodi', function($row) {
                return $row->mahasiswa->prodi ? $row->mahasiswa->prodi->jenjang.' - '.$row->mahasiswa->prodi->nama : '';
            })
            ->addColumn('mahasiswa', function($row) {
                return $row->mahasiswa->nama ?? '';
            })
            ->addColumn('mata_kuliah', function($row) {
                return $row->mk->nama ?? '';
            })
            ->addColumn('dosen_pengampu', function($row) {
                return $row->user->name ?? '';
            })
            ->addColumn('semester', function($row) {
                return $row->semester->kode ?? '';
            })
            ->filterColumn('semester', function($query, $keyword) {
                $query->where('semesters.kode', 'like', "%{$keyword}%");
            })
            ->filterColumn('mahasiswa', function($query, $keyword) {
                $query->where('mahasiswas.nama', 'like', "%{$keyword}%");
            })
            ->filterColumn('prodi', function($query, $keyword) {
                $query->where('prodis.nama', 'like', "%{$keyword}%");
            })
            ->filterColumn('mata_kuliah', function($query, $keyword) {
                $query->where('mks.nama', 'like', "%{$keyword}%");
            })
            ->filterColumn('dosen_pengampu', function($query, $keyword) {
                $query->where('users.name', 'like', "%{$keyword}%");
            })
            ->orderColumn('mahasiswa', 'mahasiswas.nama $1')
            ->orderColumn('mata_kuliah', 'mks.nama $1')
            ->orderColumn('dosen_pengampu', 'users.name $1')
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(KontrakMk $model): QueryBuilder
    {
        $prodiIds = collect($this->prodi_ids ?? [])->filter()->values();

        if ($prodiIds->isEmpty() && auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole('prodi') || $user->hasRole('pimpinan prodi')) {
                $prodiIds = JoinProdiUser::query()
                    ->where('user_id', $user->id)
                    ->pluck('prodi_id')
                    ->filter()
                    ->unique()
                    ->values();
            }
        }

        $query = $model->newQuery()
            ->leftJoin('mahasiswas', 'kontrak_mks.mahasiswa_id', '=', 'mahasiswas.id')
            ->leftJoin('mks', 'kontrak_mks.mk_id', '=', 'mks.id')
            ->leftJoin('users', 'kontrak_mks.user_id', '=', 'users.id')
            ->select([
                'kontrak_mks.*',
                'mahasiswas.nama as mahasiswa_nama',
                'mks.nama as mk_nama',
                'users.name as user_name'
            ])
            ->with(['mahasiswa', 'mk', 'mk.cpmks', 'user']);

        // Filter berdasarkan prodi jika ada
        if ($prodiIds->isNotEmpty()) {
            $query->whereIn('mahasiswas.prodi_id', $prodiIds->all());
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('kontrakmks-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom("<'row mb-2'<'col-auto'B><'col-auto'f><'col-auto'l>>" .
                        "rt" .
                        "<'row mt-2 float-end'<'col-auto'i><'col-auto'p>>")
                    ->parameters([
                        'language' => [
                            'search' => 'Cari:',
                            'lengthMenu' => 'Tampilkan _MENU_ entri per halaman',
                            'info' => 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                            'infoEmpty' => 'Menampilkan 0 sampai 0 dari 0 entri',
                            'infoFiltered' => '(disaring dari _MAX_ total entri)',
                            'zeroRecords' => 'Data tidak ditemukan',
                            'emptyTable' => 'Tidak ada data tersedia',
                            'processing' => 'Memproses...',
                            'loadingRecords' => 'Memuat...',
                            'paginate' => [
                                'first' => 'Pertama',
                                'last' => 'Terakhir',
                                'next' => 'Berikutnya',
                                'previous' => 'Sebelumnya',
                            ],
                            'aria' => [
                                'sortAscending' => ': aktifkan untuk mengurutkan kolom naik',
                                'sortDescending' => ': aktifkan untuk mengurutkan kolom turun',
                            ],
                        ],
                    ])
                    ->orderBy(0, 'asc')
                    ->selectStyleSingle()
                    ->setTableAttribute('class', 'table table-striped table-bordered table-hover')
                    ->buttons([
                        Button::make([
                                        'text'   => '<i class="bi bi-plus-circle"></i> Add',
                                        'className' => 'btn btn-primary',
                                        'action' => 'function(e, dt, node, config){ const modal = document.getElementById("modalCreateKontrakmk"); if(modal && window.bootstrap){ bootstrap.Modal.getOrCreateInstance(modal).show(); } }',
                                    ]),
                        Button::make('reset'),
                        Button::make('reload'),
                        Button::make([
                                        'text'   => '<i class="bi bi-upload"></i> Import',
                                        'className' => 'btn btn-success',
                                        'action' => 'function(e, dt, node, config){ window.location.href = "'.route('settings.import.kontrakmks').'"; }',
                                    ]),
                                ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [
            Column::computed('mahasiswa')
                ->title('mahasiswa')
                ->searchable(true)
                ->orderable(true),
            Column::computed('mata_kuliah')
                ->title('mata kuliah')
                ->searchable(true)
                ->orderable(true),
            Column::computed('dosen_pengampu')
                ->title('dosen pengampu')
                ->searchable(true)
                ->orderable(true),
            Column::computed('semester')
                ->title('semester')
                ->searchable(true)
                ->orderable(true),
            Column::make('kelas'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->searchable(false)
                  ->orderable(false)
                  ->width(50)
                  ->addClass('text-center'),
        ];

        $isPimpinanProdi = auth()->check() && auth()->user()->hasRole('pimpinan prodi');
        if (!$isPimpinanProdi) {
            array_splice($columns, 1, 0, [
                Column::computed('prodi')
                    ->title('prodi')
                    ->searchable(true)
                    ->orderable(true),
            ]);
        }

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'KontrakMks_' . date('YmdHis');
    }
}
