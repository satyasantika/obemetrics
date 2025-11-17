<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
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
                $action .= ' <div class="col-auto"><a href="'.route('users.edit',$row->id).'" class="btn btn-primary btn-sm action"><i class="bi bi-pencil-square"></i></a></div>';
                // $action .= ' <div class="col-auto"><a href="'.route('userroles.edit',$row->id).'" class="btn btn-outline-primary btn-sm action">R</a>';
                // $action .= ' <a href="'.route('userpermissions.edit',$row->id).'" class="btn btn-outline-primary btn-sm action">P</a>';
                $action .= '</div>';
                return $action;
            })
            ->editColumn('username', function($row){
                $color = $row->hasRole('active-user') ? 'text-primary':'text-dark';
                $icon = $row->hasRole('active-user') ? 'person-fill-check':'person-';
                $username = '<span class="'.$color.'"> '.$row->username.'<i class="bi bi-'.$icon.'"></i> </span>';
                $activationbutton = '<form id="activation-form'.$row->id.'" action='.route('users.activation',$row->id).' method="POST">
                    <input type="hidden" name="_token" value='.csrf_token().'>
                    <button type="submit" class="btn btn-'.(!$row->hasRole('active-user') ? 'outline-success' : 'outline-danger').' btn-sm"
                    data-bs-toggle="tooltip" title data-bs-original-title="'.($row->hasRole('active-user') ? 'non-aktivkan' : 'aktivkan').'">
                    <i class="bi bi-'.($row->hasRole('active-user') ? 'arrow-down':'arrow-up').'"></i>
                    </button>'.$username.'
                    </form>';
                return $activationbutton;
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
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('users-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(0, 'asc')
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('add'),
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
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
            Column::make('name'),
            Column::make('username'),
            Column::make('updated_at'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(50)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
