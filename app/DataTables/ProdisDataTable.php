<?php

namespace App\DataTables;

use App\Models\Prodi;
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
                $action = '<div class="row">';
                $action .= ' <div class="col-auto"><button type="button" class="btn btn-primary btn-sm action js-prodi-modal-trigger" data-bs-toggle="modal" data-bs-target="#modalEditProdi" data-prodi-id="'.$row->id.'" data-prodi-kode-prodi="'.$kodeProdi.'" data-prodi-nama="'.$nama.'" data-prodi-jenjang="'.$jenjang.'" data-prodi-kode-pddikti="'.$kodePddikti.'" data-prodi-singkat="'.$singkat.'" data-prodi-pt="'.$pt.'" data-prodi-fakultas="'.$fakultas.'" data-prodi-visi-misi="'.$visiMisi.'" title="Edit data prodi"><i class="bi bi-pencil-square"></i></button></div>';
                $action .= ' <div class="col-auto"><a href="'.route('prodis.joinprodiusers.index',$row->id).'" class="btn btn-success btn-sm action" data-bs-toggle="tooltip" title="SET User"><i class="bi bi-person-gear"></i> User</a></div>';
                $action .= '</div>';
                return $action;
            })
            ->editColumn('updated_at', function($row) {
                return $row->updated_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['action','username'])
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
            Column::make('updated_at'),
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
