<?php

namespace App\DataTables;

use App\Models\User;
use App\Models\Prodi;
use App\Models\JoinProdiUser;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
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
                static $prodiKurikulumIds = null;
                static $prodiMkIds = null;
                static $lockedUserIds = [];

                if ($prodiKurikulumIds === null || $prodiMkIds === null) {
                    $prodi = Prodi::query()->with('kurikulums.mks:id,kurikulum_id')->find($this->prodi_id);
                    $prodiKurikulumIds = $prodi?->kurikulums?->pluck('id')->values()->all() ?? [];
                    $prodiMkIds = $prodi?->kurikulums?->pluck('mks')->flatten()->pluck('id')->values()->all() ?? [];
                }

                if (!array_key_exists($row->user_id, $lockedUserIds)) {
                    $isUsedInJoinMkUser = \App\Models\JoinMkUser::query()
                        ->where('user_id', $row->user_id)
                        ->whereIn('kurikulum_id', $prodiKurikulumIds)
                        ->exists();

                    $isUsedInKontrak = \App\Models\KontrakMk::query()
                        ->where('user_id', $row->user_id)
                        ->whereIn('mk_id', $prodiMkIds)
                        ->exists();

                    $lockedUserIds[$row->user_id] = $isUsedInJoinMkUser || $isUsedInKontrak;
                }

                $statusPimpinan = (bool) ($row->status_pimpinan ?? false);
                $canDelete = !$lockedUserIds[$row->user_id];

                $action = '<div class="row">';
                $action .= ' <div class="col-auto"><button type="button" class="btn btn-primary btn-sm action" data-bs-toggle="modal" data-bs-target="#modalEditJoinProdiUser" data-joinprodiuser-id="'.$row->id.'" data-joinprodiuser-username="'.e($row->user->name ?? '').'" data-joinprodiuser-status-pimpinan="'.($statusPimpinan ? '1' : '0').'" data-joinprodiuser-can-delete="'.($canDelete ? '1' : '0').'" title="Edit data user prodi"><i class="bi bi-pencil-square"></i></button></div>';
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
            ->addColumn('nama_user', function($row) {
                return $row->user->name ?? '';
            })
            ->editColumn('status_pimpinan', function ($row) {
                return (bool) ($row->status_pimpinan ?? false) ? 'Ya' : '-';
            })
            ->filterColumn('nama_user', function($query, $keyword) {
                $query->where('users.name', 'like', "%{$keyword}%");
            })
            ->orderColumn('nama_user', 'users.name $1')
            ->filterColumn('status_pimpinan', function ($query, $keyword) {
                $normalized = mb_strtolower(trim((string) $keyword));
                if ($normalized === 'ya') {
                    $query->where('join_prodi_users.status_pimpinan', true);
                    return;
                }

                if ($normalized === '-' || $normalized === 'tidak' || $normalized === 'no') {
                    $query->where('join_prodi_users.status_pimpinan', false);
                }
            })
            ->rawColumns(['action','nama_user'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(JoinProdiUser $model): QueryBuilder
    {
        $table = $model->getTable();

        return $model->newQuery()->where($table . '.prodi_id', $this->prodi_id)
            ->leftJoin('users', $table . '.user_id', '=', 'users.id')
            ->select([
                $table . '.*',
                'users.name as user_name'
            ])
            ->with(['user']);
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
                        // Button::make('add'),
                        Button::make([
                                        'text'   => '<i class="bi bi-plus-circle"></i> User',
                                        'className' => 'btn btn-success',
                                        'action' => 'function(e, dt, node, config){ window.location.href = "'.route('prodis.joinprodiusers.create',$this->prodi_id).'"; }',
                                    ]),
                        Button::make('reset'),
                        Button::make('reload'),
                        Button::make([
                                        'text'   => '<i class="bi bi-upload"></i> Banyak User',
                                        'className' => 'btn btn-success',
                                        'action' => 'function(e, dt, node, config){ window.location.href = "'.route('settings.import.admin-master', ['target' => 'joinprodiusers']).'"; }',
                                    ]),
                                ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('nama_user')
                ->title('Nama User')
                ->searchable(true)
                ->orderable(true),
            Column::make('role'),
            Column::make('status_pimpinan')->title('status pimpinan'),
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
