@extends('layouts.app')

@push('title')
419 Page Expired
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4 p-lg-5">
                    <div class="display-4 fw-bold text-warning">419</div>
                    <h5 class="mt-2 mb-1">Sesi Berakhir</h5>
                    <p class="text-muted mb-4">Halaman kedaluwarsa. Silakan ulangi proses dari awal.</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ auth()->check() ? route('home') : url('/') }}" class="btn btn-primary">
                            <i class="bi bi-house-door"></i> Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
