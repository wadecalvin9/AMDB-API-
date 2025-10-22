@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ $meta['meta']['name'] ?? 'Unknown' }}</h2>
    <img src="{{ $meta['meta']['poster'] ?? '' }}" class="img-fluid mb-3">
    <p>{{ $meta['meta']['description'] ?? '' }}</p>

    <a href="{{ route('addons.stream', [$base64, $type, $id]) }}" class="btn btn-success">Show Streams</a>
</div>
@endsection
