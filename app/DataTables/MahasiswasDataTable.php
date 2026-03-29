<?php

namespace App\DataTables;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
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
                $nama = e((string) $row->nama);
                $nim = e((string) $row->nim);
                $angkatan = e((string) ($row->angkatan ?? ''));
                $prodiId = e((string) ($row->prodi_id ?? ''));
                $email = e((string) ($row->email ?? ''));
                $phone = e((string) ($row->phone ?? ''));
                $action = '<div class="row">';
                $action .= ' <div class="col-auto">';
                $action .= '  <button type="button" class="btn btn-primary btn-sm action js-mahasiswa-modal-trigger" data-bs-toggle="modal" data-bs-target="#modalEditMahasiswa" data-mahasiswa-id="'.$row->id.'" data-mahasiswa-nama="'.$nama.'" data-mahasiswa-nim="'.$nim.'" data-mahasiswa-angkatan="'.$angkatan.'" data-mahasiswa-prodi-id="'.$prodiId.'" data-mahasiswa-email="'.$email.'" data-mahasiswa-phone="'.$phone.'" title="Edit Mahasiswa"><i class="bi bi-pencil-square"></i></button>';
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
        $query = $model->newQuery()->with('prodi');
        $user = auth()->user();

        if ($user && $user->hasRole('pimpinan prodi')) {
            $managedProdiIds = $user->joinProdiUsers()
                ->where('status_pimpinan', true)
                ->pluck('prodi_id')
                ->filter()
                ->unique()
                ->values();

            if ($managedProdiIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereIn('prodi_id', $managedProdiIds);
        }

        return $query;
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
                                        'action' => 'function(e, dt, node, config){ const modal = document.getElementById("modalCreateMahasiswa"); if(modal && window.bootstrap){ bootstrap.Modal.getOrCreateInstance(modal).show(); } }',
                                    ]),
                        Button::make('reset'),
                        Button::make('reload'),
                        Button::make([
                                        'text'   => '<i class="bi bi-upload"></i> Import',
                                        'className' => 'btn btn-success',
                                        'action' => 'function(e, dt, node, config){ window.location.href = "'.route('settings.import.mahasiswas').'"; }',
                                    ]),
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
            // Column::make('updated_at'),
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
        return 'Mahasiswas_' . date('YmdHis');
    }
}
