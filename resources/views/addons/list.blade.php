@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Installed Addons</h2>

    @if($addons->isEmpty())
        <div class="alert alert-info">No addons installed yet.</div>
    @else
        <ul class="list-group">
            @foreach($addons as $addon)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $addon->name }}</strong><br>
                        <small>{{ $addon->manifest_url }}</small>
                    </div>
                    <a href="{{ route('addons.load', ['url' => $addon->manifest_url]) }}" class="btn btn-primary btn-sm">
                        Open
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
