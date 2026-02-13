<?php

namespace App\DataTables;

use App\Models\KontrakMk;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
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
                $action = '<div class="row">';
                $action .= ' <div class="col-auto">';
                $action .= '  <a href="'.route('kontrakmks.edit',$row->id).'" class="btn btn-primary btn-sm action" data-bs-toggle="tooltip" title="Edit KontrakMk"><i class="bi bi-pencil-square"></i></a>';
                $action .= ' </div>';
                $action .= '</div>';
                return $action;
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
                return $row->mk->cpmks->first()->semester ?? '';
            })
            ->filterColumn('mahasiswa', function($query, $keyword) {
                $query->where('mahasiswas.nama', 'like', "%{$keyword}%");
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
        if (!empty($this->prodi_ids)) {
            $query->whereIn('mahasiswas.prodi_id', $this->prodi_ids);
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
                    ->orderBy(0, 'asc')
                    ->selectStyleSingle()
                    ->setTableAttribute('class', 'table table-striped table-bordered table-hover')
                    ->buttons([
                        Button::make('add'),
                        Button::make('reset'),
                        Button::make('reload'),
                        Button::make([
                                        'text'   => '<i class="bi bi-upload"></i> Import',
                                        'className' => 'btn btn-success',
                                        'action' => 'function(e, dt, node, config){ window.location.href = "'.route('setting.import.kontrakmks').'"; }',
                                    ]),
                                ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
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
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->searchable(false)
                  ->orderable(false)
                  ->width(175)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'KontrakMks_' . date('YmdHis');
    }
}
