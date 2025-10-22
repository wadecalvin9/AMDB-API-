@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ $manifest['name'] ?? 'Unknown Addon' }}</h2>
    <p>{{ $manifest['description'] ?? 'No description available.' }}</p>
    <h4>Catalogs</h4>
    <ul>
        @foreach ($manifest['catalogs'] ?? [] as $catalog)
            <li>
                <a href="{{ route('addons.catalog', [$encoded, $catalog['type'], $catalog['id']]) }}">
                    {{ $catalog['name'] ?? $catalog['id'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
@endsection
