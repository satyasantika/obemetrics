<?php

namespace App\DataTables;

use App\Models\User;
use App\Models\JoinProdiUser;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class JoinProdiUsersDataTable extends DataTable
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
                $action .= ' <div class="col-auto"><a href="'.route('joinprodiusers.edit',$row->id).'" class="btn btn-primary btn-sm action" data-bs-toggle="tooltip" title="Edit data user prodi"><i class="bi bi-pencil-square"></i></a></div>';
                $action .= '</div>';
                return $action;
            })
            ->editColumn('updated_at', function($row) {
                return $row->updated_at->format('Y-m-d H:i:s');
            })
            ->AddColumn('role', function($row) {
                $role = User::find($row->user_id)->getRoleNames();
                return $role->join(', ');
            })
            ->editColumn('user_id', function($row) {
                return $row->user->name;
            })
            ->rawColumns(['action','username'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(JoinProdiUser $model): QueryBuilder
    {
        return $model->where('prodi_id',$this->prodi_id)->newQuery();
        // return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('joinprodiusers-table')
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
            // Column::make('prodi_id'),
            Column::make('user_id')->title('nama user'),
            Column::make('role'),
            Column::make('status'),
            Column::make('updated_at'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(80)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Joinprodiusers_' . date('YmdHis');
    }
}
