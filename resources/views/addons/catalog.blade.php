@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Catalog: {{ $id }}</h2>
    <div class="row">
        @foreach ($catalog['metas'] ?? [] as $item)
        <div class="col-md-3 mb-4">
            <div class="card">
                <img src="{{ $item['poster'] ?? '' }}" class="card-img-top">
                <div class="card-body">
                    <h5 class="card-title">{{ $item['name'] }}</h5>
                    <a href="{{ route('addons.meta', [$base64, $type, $item['id']]) }}" class="btn btn-sm btn-primary">Details</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
