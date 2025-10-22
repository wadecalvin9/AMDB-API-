@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">
        Streams for {{ strtoupper($type) }} ({{ $movie_id }})
        <small class="text-muted">via {{ $addon->name }}</small>
    </h3>

    @if (empty($streams))
        <div class="alert alert-warning">No streams found for this title.</div>
    @else
        <div class="list-group">
            @foreach ($streams as $stream)
                <div class="list-group-item">
                    <strong>{{ $stream['title'] ?? 'Untitled Stream' }}</strong><br>
                    <small>Source: {{ $stream['name'] ?? 'Unknown' }}</small><br>
                    <small>Type: {{ $stream['behaviorHints']['bingeGroup'] ?? 'N/A' }}</small><br>
                    @if(isset($stream['url']))
                        <a href="{{ $stream['url'] }}" target="_blank" class="btn btn-primary btn-sm mt-2">
                            Open Stream
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
