@extends('layouts.app')

@section('content')
<div class="container-fluid py-5">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-light mb-1">
                <i class="fas fa-heart text-danger me-2"></i>My Favorites
            </h2>
            <p class="text-muted mb-0 small">Your hand-picked collection of films and shows</p>
        </div>
        <a href="{{ route('discover') }}" class="btn btn-gradient px-4 rounded-pill">
            <i class="fas fa-compass me-1"></i> Discover More
        </a>
    </div>

    <!-- Empty State -->
    @if($favorites->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fas fa-heart-broken fa-3x mb-3 text-secondary"></i>
            <h5 class="mb-2">No favorites yet</h5>
            <p class="small text-muted">Click ❤️ on any movie or TV show to add it here.</p>
            <a href="{{ route('discover') }}" class="btn btn-outline-light rounded-pill mt-3 px-4">
                Browse Movies
            </a>
        </div>
    @else
        <!-- Grid -->
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4">
            @foreach($favorites as $item)
                @php
                    $detailsRoute = $item->type === 'tv'
                        ? route('tv.show', ['id' => $item->item_id])
                        : route('movie.show', ['id' => $item->item_id]);
                    $poster = $item->metadata['poster'] ?? asset('images/no-poster.jpg');
                @endphp

                <div class="col">
                    <a href="{{ $detailsRoute }}" class="text-decoration-none text-light">
                        <div class="movie-card position-relative overflow-hidden rounded-4 shadow-sm border-0">
                            <img src="{{ $poster }}" class="w-100 poster" alt="{{ $item->title }}" loading="lazy">

                            <!-- Overlay on hover -->
                            <div class="overlay d-flex flex-column justify-content-end p-3">
                                <div class="text-start">
                                    <h6 class="fw-semibold mb-1 text-truncate">{{ $item->title }}</h6>
                                    <span class="badge bg-gradient small text-uppercase">{{ $item->type }}</span>
                                </div>
                            </div>

                            <!-- Floating heart -->
                            <div class="fav-badge">
                                <i class="fas fa-heart text-danger"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('styles')
<style>
    body { background-color: #0b0b0b; }

    /* 🔥 Fancy gradient button */
    .btn-gradient {
        background: linear-gradient(90deg, #5b4bff, #6c63ff);
        color: #fff !important;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-gradient:hover {
        background: linear-gradient(90deg, #6c63ff, #5b4bff);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 99, 255, 0.4);
    }

    /* 🎞️ Movie card styling */
    .movie-card {
        background-color: #141414;
        border-radius: 1rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .movie-card:hover {
        transform: scale(1.04);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
    }

    .poster {
        border-radius: 1rem;
        height: 340px;
        object-fit: cover;
        filter: brightness(0.9);
        transition: filter 0.3s ease;
    }
    .movie-card:hover .poster {
        filter: brightness(1);
    }

    /* 💫 Overlay fade effect */
    .overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.85), rgba(0,0,0,0.1));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .movie-card:hover .overlay {
        opacity: 1;
    }

    /* ❤️ Floating favorite badge */
    .fav-badge {
        position: absolute;
        top: 10px;
        right: 12px;
        font-size: 1.2rem;
        z-index: 3;
        opacity: 0.9;
    }

    .badge.bg-gradient {
        background: linear-gradient(90deg, #5b4bff, #6c63ff);
        border: none;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
    }
</style>
@endpush
@endsection
