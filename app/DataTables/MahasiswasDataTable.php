<?php

namespace App\DataTables;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class MahasiswasDataTable extends DataTable
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
                $action .= '  <a href="'.route('mahasiswas.edit',$row->id).'" class="btn btn-primary btn-sm action" data-bs-toggle="tooltip" title="Edit Mahasiswa"><i class="bi bi-pencil-square"></i></a>';
                $action .= ' </div>';
                $action .= '</div>';
                return $action;
            })
            ->editColumn('prodi_id', function($row) {
                return $row->prodi ? $row->prodi->jenjang.' - '.$row->prodi->nama : '';
            })
            ->editColumn('updated_at', function($row) {
                return $row->updated_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Mahasiswa $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('mahasiswas-table')
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
                        Button::make('reload')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('nim'),
            Column::make('nama'),
            Column::make('prodi_id')->title('Prodi'),
            Column::make('updated_at'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(175)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Mahasiswas_' . date('YmdHis');
    }
}
