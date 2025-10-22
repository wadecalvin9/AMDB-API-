@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 fw-bold text-light">
        <i class="fas fa-bookmark text-primary me-2"></i>Your Watchlist
    </h2>

    @if ($watchlist->isEmpty())
        <div class="text-center text-secondary my-5">
            <p class="fs-5">Your watchlist is empty.</p>
            <p class="small">Click ➕ on a movie or TV show to save it here!</p>
        </div>
    @else
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4">
            @foreach($watchlist as $item)
                @php
                    $detailsRoute = $item->type === 'tv'
                        ? route('tv.show', ['id' => $item->item_id])
                        : route('movie.show', ['id' => $item->item_id]);

                    $poster = $item->metadata['poster'] ?? asset('images/no-poster.jpg');
                @endphp

                <div class="col">
                    <div class="movie-card position-relative">
                        <!-- Toggle Button -->
                        <button
                            class="favorite-btn toggle-watchlist active"
                            data-id="{{ $item->item_id }}"
                            data-type="{{ $item->type }}"
                            title="Remove from Watchlist">
                            <i class="fas fa-bookmark"></i>
                        </button>

                        <!-- Poster + Title -->
                        <a href="{{ $detailsRoute }}" class="text-decoration-none text-light">
                            <img src="{{ $poster }}" alt="{{ $item->title }}" loading="lazy">
                            <div class="card-body">
                                <h6 class="text-truncate">{{ $item->title }}</h6>
                                <small>{{ strtoupper($item->type) }}</small>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.toggle-watchlist').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();

            const movieId = btn.dataset.id;
            const type = btn.dataset.type;
            const card = btn.closest('.col');

            try {
                const response = await fetch("{{ route('watchlist.toggle') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ item_id: movieId, type })
                });

                const data = await response.json();

                if (data.status === "removed") {
                    // Smooth remove animation
                    card.style.transition = "all 0.3s ease";
                    card.style.transform = "scale(0.9)";
                    card.style.opacity = "0";
                    setTimeout(() => card.remove(), 300);
                }
            } catch (error) {
                console.error('Watchlist toggle failed:', error);
            }
        });
    });
});
</script>
@endpush
