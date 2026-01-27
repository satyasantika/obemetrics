@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    @stack('header')
                </div>
                <div class="card-body">
                    @include('layouts.alert')
                    @stack('body')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
