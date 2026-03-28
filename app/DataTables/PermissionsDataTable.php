<?php

namespace App\DataTables;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Services\DataTable;

class PermissionsDataTable extends DataTable
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
                $name = e((string) $row->name);
                $guard = e((string) ($row->guard_name ?? ''));
                $action = ' ';
                $action .= ' <button type="button" class="btn btn-primary btn-sm action js-permission-modal-trigger" data-bs-toggle="modal" data-bs-target="#modalEditPermission" data-permission-id="'.$row->id.'" data-permission-name="'.$name.'" data-permission-guard="'.$guard.'" title="Edit Permission"><i class="bi bi-pencil-square"></i></button>';
                return $action;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Permission $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('permissions-table')
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
                    ->orderBy(1,'asc')
                    ->selectStyleSingle()
                    ->setTableAttribute('class', 'table table-striped table-bordered table-hover')
                    ->buttons([
                        Button::make([
                            'text'   => '<i class="bi bi-plus-circle"></i> Add',
                            'className' => 'btn btn-primary',
                            'action' => 'function(e, dt, node, config){ const modal = document.getElementById("modalCreatePermission"); if(modal && window.bootstrap){ bootstrap.Modal.getOrCreateInstance(modal).show(); } }',
                        ]),
                        Button::make('reset'),
                        Button::make('reload')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
            Column::make('name'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Permissions_' . date('YmdHis');
    }
}
