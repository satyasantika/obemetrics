<?php

namespace App\DataTables;

use App\Models\Prodi;
use App\States\Prodi\Draft as ProdiDraft;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Services\DataTable;

class ProdisDataTable extends DataTable
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
                $kodeProdi = e((string) ($row->kode_prodi ?? ''));
                $nama = e((string) $row->nama);
                $jenjang = e((string) ($row->jenjang ?? ''));
                $kodePddikti = e((string) ($row->kode_pddikti ?? ''));
                $singkat = e((string) ($row->singkat ?? ''));
                $pt = e((string) ($row->pt ?? ''));
                $fakultas = e((string) ($row->fakultas ?? ''));
                $visiMisi = e((string) ($row->visi_misi ?? ''));

                $isDraft = $row->status instanceof ProdiDraft;
                $userBtnClass = $isDraft ? 'btn-secondary' : 'btn-success';
                $userBtnTitle = $isDraft ? 'Belum ada pengelola — klik untuk set user' : 'SET User';

                $action = '<div class="row">';
                $action .= ' <div class="col-auto"><button type="button" class="btn btn-primary btn-sm action js-prodi-modal-trigger" data-bs-toggle="modal" data-bs-target="#modalEditProdi" data-prodi-id="'.$row->id.'" data-prodi-kode-prodi="'.$kodeProdi.'" data-prodi-nama="'.$nama.'" data-prodi-jenjang="'.$jenjang.'" data-prodi-kode-pddikti="'.$kodePddikti.'" data-prodi-singkat="'.$singkat.'" data-prodi-pt="'.$pt.'" data-prodi-fakultas="'.$fakultas.'" data-prodi-visi-misi="'.$visiMisi.'" title="Edit data prodi"><i class="bi bi-pencil-square"></i></button></div>';
                $action .= ' <div class="col-auto"><a href="'.route('prodis.joinprodiusers.index',$row->id).'" class="btn '.$userBtnClass.' btn-sm action" data-bs-toggle="tooltip" title="'.$userBtnTitle.'"><i class="bi bi-person-gear"></i> User</a></div>';
                $action .= '</div>';
                return $action;
            })
            ->editColumn('status', function($row) {
                $st = $row->status;
                if ($st === null) {
                    return '<span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-1">—</span>';
                }

                [$bgClass, $textClass, $borderClass, $icon] = match (true) {
                    $st instanceof \App\States\Prodi\Draft    => ['bg-warning-subtle',  'text-warning-emphasis',  'border-warning-subtle',  'bi-hourglass-split'],
                    $st instanceof \App\States\Prodi\Aktif    => ['bg-success-subtle',  'text-success-emphasis',  'border-success-subtle',  'bi-check-circle-fill'],
                    $st instanceof \App\States\Prodi\NonAktif => ['bg-danger-subtle',   'text-danger-emphasis',   'border-danger-subtle',   'bi-slash-circle-fill'],
                    default                                    => ['bg-secondary-subtle','text-secondary-emphasis','border-secondary-subtle', 'bi-circle'],
                };

                $label = e($st->label());
                return '<span class="badge rounded-pill '.$bgClass.' '.$textClass.' border '.$borderClass.' px-2 py-1" style="font-size:0.78rem;font-weight:600;letter-spacing:0.02em;">'
                    . '<i class="bi '.$icon.' me-1"></i>'.$label.'</span>';
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Prodi $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('prodis-table')
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
                                        'action' => 'function(e, dt, node, config){ const modal = document.getElementById("modalCreateProdi"); if(modal && window.bootstrap){ bootstrap.Modal.getOrCreateInstance(modal).show(); } }',
                                    ]),
                        Button::make('reset'),
                        Button::make('reload'),
                        Button::make([
                                        'text'   => '<i class="bi bi-upload"></i> Import',
                                        'className' => 'btn btn-success',
                                        'action' => 'function(e, dt, node, config){ window.location.href = "'.route('settings.import.admin-master', ['target' => 'prodis']).'"; }',
                                    ]),
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('kode_prodi'),
            Column::make('kode_pddikti'),
            Column::make('nama'),
            Column::make('status'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(180)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Prodis_' . date('YmdHis');
    }
}
