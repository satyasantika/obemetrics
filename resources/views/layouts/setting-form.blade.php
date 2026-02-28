@extends('layouts.panel')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                @stack('header')
            </div>
            <div class="card-body">
                 @stack('body')
            </div>
        </div>
    </div>
</div>
@endsection
