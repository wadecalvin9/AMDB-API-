<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'CineStream') }}</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- 🌌 Theme Styles -->
  <style>
    :root {
      --primary-color: #dc3545;
      --accent-color: #ff4757;
      --background-dark: #0f0f0f;
      --surface-dark: #1c1c1c;
      --text-light: #f5f5f5;
      --text-muted: #b0b0b0;
      --radius: 0.75rem;
      --transition: 0.3s ease;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--background-dark);
      color: var(--text-light);
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* 🔝 Navbar */
    .navbar {
      background: rgba(18, 18, 18, 0.9);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255,255,255,0.08);
      transition: background 0.3s ease;
    }

    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: var(--primary-color);
      letter-spacing: -0.5px;
    }

    .navbar-brand:hover {
      color: var(--accent-color);
    }

    .nav-link {
      color: var(--text-muted);
      font-weight: 500;
      margin-right: 1rem;
      transition: color 0.2s;
    }

    .nav-link:hover, .nav-link.active {
      color: var(--text-light);
    }

    /* 🧱 Main Container */
    main {
      padding-top: 80px;
      min-height: calc(100vh - 120px);
    }

    /* 🎬 Card hover styling */
    .movie-card {
      background-color: var(--surface-dark);
      border: none;
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .movie-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.4);
    }

    .movie-card img {
      border-top-left-radius: var(--radius);
      border-top-right-radius: var(--radius);
      height: 360px;
      object-fit: cover;
      width: 100%;
    }

    /* ❤️ Watchlist / Favorite buttons */
    .favorite-btn, .watchlist-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(0, 0, 0, 0.7);
      border: none;
      color: #fff;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s ease, background 0.2s ease;
      z-index: 2;
    }

    .favorite-btn:hover, .watchlist-btn:hover {
      background: var(--primary-color);
      transform: scale(1.1);
    }

    .favorite-btn.active i,
    .watchlist-btn.active i {
      color: var(--accent-color);
    }

    /* 🧭 Footer */
    footer {
      text-align: center;
      padding: 2rem 0;
      color: var(--text-muted);
      border-top: 1px solid rgba(122, 122, 122, 0.1);
      margin-top: 4rem;
    }

    footer a {
      color: var(--primary-color);
      text-decoration: none;
    }

    footer a:hover {
      color: var(--accent-color);
    }
  </style>

  @stack('styles')
</head>
<body>
  <!-- 🔝 Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand" href="{{ route('discover') }}">
        <i class="fas fa-play-circle me-2"></i>{{ config('app.name', 'CineStream') }}
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('discover') ? 'active' : '' }}" href="{{ route('discover') }}" id="startSync">
              <i class="fas fa-compass me-1"></i>Discover
            </a>
          </li>

          @auth
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('watchlist.index') ? 'active' : '' }}" href="{{ route('watchlist.index') }}">
              <i class="fas fa-bookmark me-1"></i>Watchlist
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('favorites.index') ? 'active' : '' }}" href="{{ route('favorites.index') }}">
              <i class="fas fa-heart me-1"></i>Favorites
            </a>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-cog me-1"></i>Settings
            </a>
            <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary">
              <li><a class="dropdown-item text-light" href="{{ route('settings.profile') }}">Profile</a></li>
              <li><a class="dropdown-item text-light" href="{{ route('settings.appearance') }}">Appearance</a></li>
              <li><a class="dropdown-item text-light" href="{{ route('settings.password') }}">Password</a></li>
              <li><hr class="dropdown-divider border-secondary"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="dropdown-item text-danger fw-semibold">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                  </button>
                </form>
              </li>
            </ul>
          </li>
          @else
          <li class="nav-item">
            <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i>Register</a>
          </li>
          @endauth
        </ul>
      </div>
    </div>
  </nav>

<script>
document.getElementById('startSync').addEventListener('click', async (e) => {
  e.preventDefault(); // stop the link from navigating immediately

  const button = e.target;
  const originalText = button.innerHTML;
  button.innerHTML = '<i class="fas fa-sync fa-spin me-1"></i> Syncing...';
  button.style.pointerEvents = 'none';

  try {
    const res = await fetch('/sync?direction=to-remote', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      }
    });

    const text = await res.text();
    let data;
    try { data = JSON.parse(text); }
    catch { throw new Error('Invalid response: ' + text.slice(0, 200)); }

    if (data.status === 'success') {
      button.innerHTML = '<i class="fas fa-check me-1"></i> Synced!';
      console.log('✅ Sync summary:', data.summary);
      setTimeout(() => window.location.href = "{{ route('discover') }}", 800);
    } else {
      button.innerHTML = '<i class="fas fa-times me-1"></i> Sync failed';
      console.error('❌ Sync error:', data);
      setTimeout(() => button.innerHTML = originalText, 1500);
    }
  } catch (err) {
    console.error('💥 Sync request error:', err);
    button.innerHTML = '<i class="fas fa-bug me-1"></i> Error';
    setTimeout(() => button.innerHTML = originalText, 1500);
  } finally {
    button.style.pointerEvents = 'auto';
  }
});
</script>





  <!-- 🧱 Main -->
  <main class="container-fluid px-4">
    @yield('content')
  </main>

  <!-- 🧭 Footer -->
  <footer>
    <p>© {{ date('Y') }} {{ config('app.name', 'CineStream') }} — Built with ❤️ for movie lovers</p>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
