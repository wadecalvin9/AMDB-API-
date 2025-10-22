@extends('layouts.app')
@section('styles')
<style>
    .poster-img {
        max-height: 500px;
        object-fit: cover;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .poster-img:hover {
        transform: scale(1.03);
        box-shadow: 0 8px 16px rgba(0,0,0,0.3);
    }
    .cast-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .cast-img:hover {
        transform: scale(1.05);
    }
    .cast-card {
        flex: 0 0 auto; /* Prevent cards from stretching */
    }
    .cast-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    .overflow-auto::-webkit-scrollbar {
        height: 8px;
    }
    .overflow-auto::-webkit-scrollbar-thumb {
        background: var(--secondary-color);
        border-radius: 4px;
    }
    .trailer-frame {
        border: 0;
        width: 100%;
        height: 100%;
    }
</style>
@endsection
@section('content')
<div class="container my-5 px-4">
    <div class="row g-4 flex-column flex-md-row">

        <!-- Poster -->
        <div class="col-md-4">
            @if($movie['poster_path'])
                <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}"
                     class="img-fluid rounded shadow-sm poster-img"
                     alt="{{ $movie['title'] ?? $movie['name'] }}">
            @else
                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height:500px;">
                    <i class="fas fa-film text-muted fs-1"></i>
                </div>
            @endif
        </div>

        <!-- Info -->
        <div class="col-md-8">
            <h1 class="mb-3">{{ $movie['title'] ?? $movie['name'] }}</h1>

            @if(!empty($movie['tagline']))
                <blockquote class=" fst-italic mb-3">"{{ $movie['tagline'] }}"</blockquote>
            @endif

            <p class="mb-1"><i class="fas fa-calendar-alt me-2"></i>{{ $movie['release_date'] ?? $movie['first_air_date'] }}</p>
            <p class="mb-1"><i class="fas fa-star me-2"></i>{{ number_format($movie['vote_average'],1) }}/10</p>

            @if(!empty($movie['genres']))
                <p class=""><i class="fas fa-tags me-2"></i>{{ implode(', ', array_map(fn($g) => $g['name'], $movie['genres'])) }}</p>
            @endif

            @if(!empty($movie['overview']))
                <div class="mb-4">
                    <h5>Overview</h5>
                    <p class="text-light">{{ $movie['overview'] }}</p>
                </div>
            @endif


            <!-- 🎬 Trailer Section -->
            @php
                $trailer = collect($movie['videos']['results'] ?? [])->firstWhere('site', 'YouTube');
            @endphp
            @if($trailer)
                <div id="trailerSection" class="mb-4">
                    <h5><i class="fab fa-youtube me-2 text-danger"></i>Trailer</h5>
                    <div class="ratio ratio-16x9 border rounded">
                        <iframe id="trailerFrame" src="https://www.youtube.com/embed/{{ $trailer['key'] }}"
                                allowfullscreen class="trailer-frame rounded"></iframe>
                    </div>
                </div>
            @endif




            <!-- TV SEASONS -->
            @if(($movie['media_type'] ?? $type ?? '') === 'tv' || isset($movie['seasons']))
                <div class="mb-4">
                    <h5>Watch Episodes</h5>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label text-muted">Season</label>
                            <select id="seasonSelect" class="form-select bg-secondary text-light border-0">
                                @foreach($movie['seasons'] as $season)
                                    @if($season['season_number'] > 0)
                                        <option value="{{ $season['season_number'] }}">{{ $season['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted">Episode</label>
                            <select id="episodeSelect" class="form-select bg-secondary text-light border-0">
                                <option>Loading...</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button id="playEpisodeBtn" class="btn btn-primary w-100" disabled>
                                <i class="fas fa-play me-1"></i> Play
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Buttons -->
            <div class="mt-4 pt-3 border-top d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('discover') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>

                @php
                    $imdbId = $movie['imdb_id'] ?? ($movie['external_ids']['imdb_id'] ?? null);
                    $contentType = $type ?? ($movie['media_type'] ?? 'movie');
                    // prepare poster url for payload
                    $posterUrl = $movie['poster_path'] ? "https://image.tmdb.org/t/p/w500{$movie['poster_path']}" : null;
                @endphp

                @if($imdbId)
                    <button class="btn btn-primary" id="loadStreamsBtn"
                            data-type="{{ $contentType }}"
                            data-id="{{ $imdbId }}">
                        <i class="fas fa-play me-1"></i> Show Streams
                    </button>
                @endif

            </div>
        </div>
    </div>
</div>
<!-- 👥 Cast Section -->
@php
    $cast = collect($movie['credits']['cast'] ?? [])->take(15);
@endphp
@if($cast->isNotEmpty())
    <div id="castSection" class="mt-5">
        <h4 class="mb-3"><i class="fas fa-users me-2"></i>Cast</h4>
        <div id="castList" class="d-flex overflow-auto gap-3 pb-3">
            @foreach($cast as $c)
                <div class="cast-card text-center bg-dark rounded shadow-sm p-2"
                     style="min-width: 120px; max-width: 120px; transition: transform 0.3s, box-shadow 0.3s;"
                     role="figure" aria-label="Cast member {{ $c['name'] }}">
                    <img src="{{ $c['profile_path'] ? 'https://image.tmdb.org/t/p/w185' . $c['profile_path'] : '/images/no-profile.png' }}"
                         class="cast-img rounded-circle mb-2"
                         alt="{{ $c['name'] }} as {{ $c['character'] }}"
                         style="width: 100px; height: 100px; object-fit: cover;">
                    <div class="small fw-bold text-light text-truncate">{{ $c['name'] }}</div>
                    <div class="small text-muted text-truncate">{{ $c['character'] ?: 'Unknown Role' }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endif


<!-- Streams Modal -->
<div class="modal fade" id="streamsModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content bg-dark text-light border-0">
      <div class="modal-header border-secondary">
        <h5 class="modal-title"><i class="fas fa-stream me-2"></i>Available Streams</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="loadingText" class="text-center py-4 d-none">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2 text-muted">Searching for streams...</p>
        </div>
        <div id="streamsList" class="list-group list-group-flush"></div>
      </div>
    </div>
  </div>
</div>


@endsection
