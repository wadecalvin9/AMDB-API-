@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Available Streams</h2>
    <ul class="list-group">
        @foreach ($streams['streams'] ?? [] as $stream)
        <li class="list-group-item">
            <strong>{{ $stream['title'] ?? 'Untitled Stream' }}</strong><br>
            <a href="{{ $stream['url'] }}" target="_blank" class="btn btn-primary btn-sm mt-2">Watch</a>
        </li>
        @endforeach
    </ul>
</div>
@endsection
