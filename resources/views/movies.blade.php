<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="container mx-auto py-10">
    <h1 class="text-4xl font-bold mb-8 text-center">Movie Explorer</h1>

    <!-- Search Form -->
    <form action="{{ route('movies.search') }}" method="GET" class="flex justify-center mb-10">
        <input
            type="text"
            name="query"
            placeholder="Search movies..."
            class="w-1/2 p-3 rounded-l-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            value="{{ request('query') }}"
        >
        <button type="submit" class="bg-indigo-600 text-white px-6 rounded-r-lg hover:bg-indigo-700 transition">Search</button>
    </form>

    <!-- Movies -->
    @if(!is_array($movies) || count($movies) === 0)
        <p class="text-center text-gray-500">No movies found.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($movies as $movie)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">

                    <!-- Poster -->
                    @if(!empty($movie['poster_path']))
                        <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}"
                             alt="{{ $movie['title'] }}"
                             class="w-full h-64 object-cover">
                    @else
                        <div class="w-full h-64 bg-gray-300 flex items-center justify-center text-gray-500">No Image</div>
                    @endif

                    <div class="p-4">
                        <!-- Title & Release -->
                        <h2 class="text-lg font-semibold">{{ $movie['title'] ?? 'No Title' }}</h2>
                        <p class="text-sm text-gray-500">{{ $movie['release_date'] ?? 'N/A' }}</p>

                        <!-- Torrentio Streams -->
                        @if(!empty($movie['streams']))
                            <div class="mt-3">
                                <h3 class="font-semibold text-sm mb-1">Streams:</h3>
                                @foreach($movie['streams'] as $stream)
                                    <a href="{{ $stream['url'] ?? '#' }}" target="_blank"
                                       class="inline-block bg-indigo-100 text-indigo-700 px-2 py-1 mr-1 mb-1 rounded text-xs hover:bg-indigo-200 transition">
                                        {{ $stream['title'] ?? ($stream['quality'] ?? 'Stream') }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

</body>
</html>
