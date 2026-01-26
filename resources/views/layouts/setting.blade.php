@extends('layouts.app')
@push('title')
    {{ isset($title) ? $title : '' }}
@endpush
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    @isset($header)
                        Manajemen {{ $header }}
                    @endisset
                    <a href="{{ route((isset($back_route)? $back_route : 'home')) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> kembali</a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning">
                            {{ session('warning') }}
                        </div>
                    @endif

                    {{ $dataTable->table()}}
                    @stack('body')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
