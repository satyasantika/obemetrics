<?php

namespace App\DataTables;

use App\Models\User;
use App\Models\ProdiUser;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class AddProdiUserDataTable extends DataTable
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
                $action = '<div class="row">';
                $action .= ' <div class="col-auto">';
                $action .= '  <form action="'.route('prodis.prodiusers.store', [$this->prodi_id]).'" method="POST" style="display:inline;">';
                $action .= '   <input type="hidden" name="_token" value="'.csrf_token().'">';
                $action .= '   <input type="hidden" name="user_id" value="'.$row->id.'">';
                $action .= '   <input type="hidden" name="prodi_id" value="'.$this->prodi_id.'">';
                $action .= '   <button type="submit" class="btn btn-primary btn-sm action" data-bs-toggle="tooltip" title="Tambah data user prodi"><i class="bi bi-plus-circle"></i></button>';
                $action .= '  </form>';
                $action .= ' </div>';
                $action .= '</div>';
                return $action;
            })
            ->editColumn('updated_at', function($row) {
                return $row->updated_at->format('Y-m-d H:i:s');
            })
            ->AddColumn('role', function($row) {
                $role = $row->getRoleNames();
                return $role->join(', ') ?? '';
            })
            ->rawColumns(['action','role'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->whereNotIn('id',ProdiUser::select('user_id')->where('prodi_id', $this->prodi_id))->newQuery()->role('dosen');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('addprodiusers-table')
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
                        // Button::make('add'),
                        Button::make('reset'),
                        Button::make('reload'),
                        Button::make([
                                        'text'   => '<i class="bi bi-left-arrow"></i> Back to User Prodi',
                                        'className' => 'btn btn-primary',
                                        'action' => 'function(e, dt, node, config){ window.location.href = "'.route('prodis.prodiusers.index',$this->prodi_id).'"; }',
                                    ]),
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
                ->width(80)
                ->addClass('text-center'),
            Column::make('name'),
            Column::make('role'),
            Column::make('nidn'),
            Column::make('updated_at'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'AddProdiUsers_' . date('YmdHis');
    }
}
