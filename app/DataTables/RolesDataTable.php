<?php

namespace App\DataTables;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Services\DataTable;

class RolesDataTable extends DataTable
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
                $action .= ' <button type="button" class="btn btn-primary btn-sm action js-role-modal-trigger" data-bs-toggle="modal" data-bs-target="#modalEditRole" data-role-id="'.$row->id.'" data-role-name="'.$name.'" data-role-guard="'.$guard.'" title="Edit Role"><i class="bi bi-pencil-square"></i></button>';
                $action .= ' <button type="button" class="btn btn-success btn-sm action js-role-modal-trigger" data-bs-toggle="modal" data-bs-target="#modalSetRolePermission" data-role-id="'.$row->id.'" data-role-name="'.$name.'" title="SET Permission"><i class="bi bi-person-gear"></i> P</button>';
                return $action;
            })
            ->rawColumns(['action','name'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Role $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('roles-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom("<'row mb-2'<'col-auto'B><'col-auto'f><'col-auto'l>>" .
                        "rt" .
                        "<'row mt-2 float-end'<'col-auto'i><'col-auto'p>>")
                    ->orderBy(1,'asc')
                    ->selectStyleSingle()
                    ->setTableAttribute('class', 'table table-striped table-bordered table-hover')
                    ->buttons([
                        Button::make([
                            'text'   => '<i class="bi bi-plus-circle"></i> Add',
                            'className' => 'btn btn-primary',
                            'action' => 'function(e, dt, node, config){ const modal = document.getElementById("modalCreateRole"); if(modal && window.bootstrap){ bootstrap.Modal.getOrCreateInstance(modal).show(); } }',
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
                  ->width(100)
                  ->addClass('text-center'),
            Column::make('name'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Roles_' . date('YmdHis');
    }
}
