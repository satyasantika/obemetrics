@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @includeWhen(auth()->user()->can('access prodi dashboard'),'dashboard.prodi')
                    @includeWhen(auth()->user()->can('access admin dashboard'),'dashboard.admin')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
