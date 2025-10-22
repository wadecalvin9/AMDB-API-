@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- 🔍 Header + Search -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-light mb-2">
            {{ $type === 'tv' ? 'Discover TV Shows' : 'Discover Movies' }}
        </h2>

        <form id="searchForm" method="GET" action="{{ route('discover') }}" autocomplete="off"
              class="d-flex rounded-pill overflow-hidden bg-dark shadow-sm border border-secondary-subtle"
              style="max-width: 400px;">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="text" name="query" value="{{ request('query') }}"
                   class="form-control bg-dark text-light border-0 rounded-start-pill ps-3"
                   placeholder="Search titles..." style="height: 48px;">
            <button type="submit" class="btn btn-danger rounded-end-pill px-4" style="height: 48px;">
                <i class="fas fa-search"></i>
            </button>

            @if (request('query'))
                <a href="{{ route('discover', ['type' => $type]) }}"
                   class="btn btn-outline-light rounded-end-pill px-3 ms-2" style="height: 48px;">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- 🎛️ Filters -->
    <form id="filterForm" method="GET" action="{{ route('discover') }}"
          class="row g-3 align-items-end bg-dark bg-opacity-75 p-4 rounded-4 shadow-sm mb-5 border border-secondary-subtle">
        <input type="hidden" name="query" value="{{ request('query') }}">

        <div class="col-md-2">
            <label class="form-label text-light small">Type</label>
            <select name="type" class="form-select bg-secondary text-light border-0 rounded-3">
                <option value="movie" {{ $type === 'movie' ? 'selected' : '' }}>Movies</option>
                <option value="tv" {{ $type === 'tv' ? 'selected' : '' }}>TV Shows</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label text-light small">Genre</label>
            <select name="with_genres" class="form-select bg-secondary text-light border-0 rounded-3">
                <option value="">All Genres</option>
                @foreach ($genres as $genre)
                    <option value="{{ $genre['id'] }}" {{ $with_genres == $genre['id'] ? 'selected' : '' }}>
                        {{ $genre['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label text-light small">Sort By</label>
            <select name="sort_by" class="form-select bg-secondary text-light border-0 rounded-3">
                <option value="popularity.desc" {{ $sort_by == 'popularity.desc' ? 'selected' : '' }}>Most Popular</option>
                <option value="release_date.desc" {{ $sort_by == 'release_date.desc' ? 'selected' : '' }}>Newest Releases</option>
                <option value="vote_average.desc" {{ $sort_by == 'vote_average.desc' ? 'selected' : '' }}>Top Rated</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label text-light small">Language</label>
            <select name="language" class="form-select bg-secondary text-light border-0 rounded-3">
                <option value="en-US" {{ $language == 'en-US' ? 'selected' : '' }}>English</option>
                <option value="fr-FR" {{ $language == 'fr-FR' ? 'selected' : '' }}>French</option>
                <option value="es-ES" {{ $language == 'es-ES' ? 'selected' : '' }}>Spanish</option>
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-danger w-100 fw-semibold shadow-sm rounded-3">
                <i class="fas fa-filter me-1"></i> Apply
            </button>
        </div>
    </form>

    <!-- 🎬 Movie Grid -->
    <div id="movie-grid" class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4">
        @forelse($movies as $item)
            @php
                $isTv = isset($item['first_air_date']);
                $title = $item['title'] ?? ($item['name'] ?? 'Untitled');
                $poster = $item['poster_path'] ? 'https://image.tmdb.org/t/p/w500'.$item['poster_path'] : null;
                $year = $item['release_date'] ?? ($item['first_air_date'] ?? null);
                $year = $year ? substr($year, 0, 4) : '—';
                $detailsRoute = $isTv
                    ? route('tv.show', ['id'=>$item['id'],'language'=>$language,'type'=>'tv'])
                    : route('movie.show', ['id'=>$item['id'],'language'=>$language,'type'=>'movie']);
            @endphp

            <div class="col movie-card-container">
                <a href="{{ $detailsRoute }}" class="text-decoration-none text-light">
                    <div class="movie-card">
                        @if ($poster)
                            <img src="{{ $poster }}" alt="{{ $title }}" loading="lazy">
                        @else
                            <div class="bg-secondary d-flex align-items-center justify-content-center rounded-4" style="height: 360px;">
                                <span class="text-muted small">No Image</span>
                            </div>
                        @endif

                        <div class="rating">⭐ {{ number_format($item['vote_average'] ?? 0, 1) }}</div>
                        <div class="play-btn"><i class="fas fa-play"></i></div>

                        <div class="overlay">
                            <h6 class="text-truncate">{{ $title }}</h6>
                            <small class="text-muted">{{ $year }}</small>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <p class="fs-5">No results found.</p>
                <p class="small">Try adjusting your filters or changing “Sort By”.</p>
            </div>
        @endforelse
    </div>

    <!-- Loader -->
    <div id="loading" class="text-center my-4" style="display: none;">
        <div class="spinner-border text-danger" role="status"></div>
    </div>
</div>

@push('styles')
<style>
    body {
        background: radial-gradient(circle at top, #0e0e0e, #000);
        color: #fff;
        overflow-x: hidden;
    }

    .movie-card-container {
        transition: transform .3s ease, filter .3s ease;
    }
    .movie-card-container:hover {
        transform: scale(1.05);
        z-index: 3;
    }

    .movie-card {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        background: #141414;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 10px rgba(0,0,0,0.4);
        cursor: pointer;
    }

    .movie-card img {
        border-radius: inherit;
        width: 100%;
        transition: transform 0.4s ease, opacity 0.4s ease;
    }
    .movie-card:hover img {
        transform: scale(1.1);
        opacity: 0.9;
    }

    .movie-card::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.9), transparent 50%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .movie-card:hover::after { opacity: 1; }

    .overlay {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: 1rem;
        z-index: 2;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .movie-card:hover .overlay { opacity: 1; }

    .movie-card h6 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
        color: #fff;
        text-shadow: 0 0 8px rgba(0,0,0,0.6);
    }

    .rating {
        position: absolute;
        top: .75rem;
        right: .75rem;
        background: rgba(0,0,0,0.7);
        padding: .25rem .5rem;
        border-radius: 12px;
        font-size: 0.8rem;
        color: #ffcc00;
    }

    .play-btn {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%) scale(0.8);
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(6px);
        border-radius: 50%;
        padding: 1rem;
        transition: all 0.3s ease;
        opacity: 0;
    }
    .movie-card:hover .play-btn {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    .play-btn i {
        font-size: 1.5rem;
        color: #ff3b3b;
    }

    .form-select:focus, .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(220,53,69,0.25);
        border-color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
let currentPage = {{ $pagination['current_page'] }};
let totalPages = {{ $pagination['total_pages'] }};
let isLoading = false;
const grid = document.getElementById('movie-grid');
const loader = document.getElementById('loading');

function extractGrid(html){
    const parser = new DOMParser();
    const doc = parser.parseFromString(html,'text/html');
    const newGrid = doc.querySelector('#movie-grid');
    return newGrid ? newGrid.innerHTML : '<p class="text-muted text-center mt-5">No results found.</p>';
}

// ♾️ Infinite Scroll
window.addEventListener('scroll',()=>{
    if(isLoading || currentPage >= totalPages) return;
    if(window.innerHeight + window.scrollY >= document.body.offsetHeight - 600) loadMore();
});

async function loadMore(){
    isLoading = true;
    loader.style.display = 'block';
    const params = new URLSearchParams(window.location.search);
    params.set('page', ++currentPage);
    const res = await fetch(`{{ route('discover') }}?${params.toString()}`);
    const html = await res.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html,'text/html');
    const newCards = doc.querySelectorAll('#movie-grid .col');
    newCards.forEach(card=>grid.appendChild(card));
    loader.style.display = 'none';
    isLoading = false;
}

// 🔍 AJAX Search
document.getElementById('searchForm').addEventListener('submit', async e=>{
    e.preventDefault();
    const params = new URLSearchParams(new FormData(e.target));
    grid.innerHTML = `<div class="text-center my-5 text-muted"><div class="spinner-border text-danger"></div><p class="mt-3">Searching...</p></div>`;
    const res = await fetch(`{{ route('discover') }}?${params.toString()}`);
    const html = await res.text();
    grid.innerHTML = extractGrid(html);
    history.pushState({},'',`?${params.toString()}`);
    currentPage = 1;
});

// ⚙️ AJAX Filters
document.getElementById('filterForm').addEventListener('submit', async e=>{
    e.preventDefault();
    const params = new URLSearchParams(new FormData(e.target));
    grid.innerHTML = `<div class="text-center my-5 text-muted"><div class="spinner-border text-danger"></div><p class="mt-3">Loading filters...</p></div>`;
    const res = await fetch(`{{ route('discover') }}?${params.toString()}`);
    const html = await res.text();
    grid.innerHTML = extractGrid(html);
    history.pushState({},'',`?${params.toString()}`);
    currentPage = 1;
});

// ⏪ Handle Browser Back/Forward
window.addEventListener('popstate', async ()=>{
    const res = await fetch(location.href);
    const html = await res.text();
    grid.innerHTML = extractGrid(html);
});
</script>
@endpush
@endsection
