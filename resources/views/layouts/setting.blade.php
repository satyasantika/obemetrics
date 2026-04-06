@extends('layouts.panel')
@push('title')
    {{ isset($title) ? $title : '' }}
@endpush
@section('content')
@if (isset($kurikulum) && $kurikulum)
    @include('components.identitas-kurikulum', ['kurikulum' => $kurikulum])
@endif
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <span>
                    @isset($header)
                        Manajemen {{ $header }}
                    @endisset
                </span>
                @isset($back_route)
                    <a href="{{ route($back_route) }}" class="btn btn-sm btn-outline-secondary ms-auto">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                @endisset
            </div>
            <div class="card-body">
                @stack('info')
                <div class="table-responsive datatable-mobile-scroll">
                    {{ $dataTable->table() }}
                </div>
                @stack('body')
            </div>
        </div>
    </div>
</div>

@include('setting._modals')
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush

@push('styles')
    <style>
        @media (max-width: 767.98px) {
            .datatable-mobile-scroll {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .datatable-mobile-scroll table.dataTable {
                width: max-content !important;
                min-width: 100% !important;
            }

            .datatable-mobile-scroll table.dataTable th,
            .datatable-mobile-scroll table.dataTable td {
                white-space: nowrap;
            }
        }
    </style>
@endpush
