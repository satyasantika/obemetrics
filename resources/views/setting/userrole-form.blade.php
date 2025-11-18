@extends('layouts.setting-form')

@push('header')
    Edit Roles untuk {{ $userrole->name }}
@endpush

@push('body')
<form id="formAction" action="{{ route('userroles.update',$userrole->id) }}" method="post">
    @csrf
    @if ($userrole->id)
        @method('PUT')
    @endif
    <div class="row mb-2">
        @foreach ($roles as $value)
            <div class="col-md-6">
                <div class="input-group mb-2">
                    <div class="input-group-text light">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="{{ $value->id }}"
                            class="form-check-input mt-0"
                            @checked(in_array($value->id, $userRoles))
                        >
                    </div>
                    <input type="text" class="form-control" value="{{ $value->name }}" aria-label="Text input with checkbox">
                </div>
            </div>
        @endforeach
    </div>
    <div class="row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary btn-sm">Save</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">Close</a>
        </div>
    </div>
</form>
@endpush
