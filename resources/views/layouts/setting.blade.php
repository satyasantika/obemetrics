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
                {{ $dataTable->table()}}
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
