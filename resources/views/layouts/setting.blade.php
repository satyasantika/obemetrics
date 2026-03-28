@extends('layouts.panel')
@push('title')
    {{ isset($title) ? $title : '' }}
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                @isset($header)
                    Manajemen {{ $header }}
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
