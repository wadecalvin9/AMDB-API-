<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CineStream</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #0f0f0f;
            --bg-card: #1a1a1d;
            --accent: #e50914;
            --accent-hover: #ff1e27;
            --text-light: #f5f5f5;
            --text-muted: #b0b0b0;
            --radius: 1rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-light);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(20,20,20,0.9);
            backdrop-filter: blur(12px);
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        nav a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            transition: all 0.3s ease;
        }

        nav a:hover {
            color: var(--text-light);
            background: var(--accent-hover);
        }

        nav a.active {
            color: var(--text-light);
            background: var(--accent);
        }

        main {
            padding: 7rem 2rem 3rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h2 {
            font-size: 2rem;
            margin: 0;
        }

        .page-header p {
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        .card {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: var(--radius);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }

        footer {
            text-align: center;
            color: var(--text-muted);
            padding: 2rem 1rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin-top: 3rem;
        }

        footer a {
            color: var(--accent);
            text-decoration: none;
        }

        footer a:hover {
            color: var(--accent-hover);
        }

        /* 🔥 Smooth fade overlay for sync */
        .sync-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--text-light);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
            z-index: 2000;
        }

        .sync-overlay.show {
            opacity: 1;
            pointer-events: all;
        }
    </style>
</head>
<body>

    <header>
        <h1><i class="fas fa-cog"></i> Settings</h1>
        <nav>
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('settings.profile') }}" class="{{ request()->routeIs('settings.profile') ? 'active' : '' }}">Profile</a>
            <a href="{{ route('settings.password') }}" class="{{ request()->routeIs('settings.password') ? 'active' : '' }}">Password</a>
        </nav>
    </header>

    <main>
        <div class="page-header">
            <h2>{{ $heading ?? 'Your Settings' }}</h2>
            <p>{{ $subheading ?? 'Manage your profile, password, and app appearance.' }}</p>
        </div>

        <div class="card">
            {{ $slot }}
        </div>
    </main>

    <footer>
        © {{ date('Y') }} CineStream — Built with ❤️ for movie lovers.
    </footer>

    <div class="sync-overlay" id="syncOverlay">
        <span><i class="fas fa-spinner fa-spin"></i> Syncing your data...</span>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const homeLink = document.querySelector('nav a[href="{{ route('home') }}"]');
        const overlay = document.getElementById('syncOverlay');
        if (!homeLink) return;

        homeLink.addEventListener('click', async (e) => {
            e.preventDefault();
            overlay.classList.add('show');

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
                try { data = JSON.parse(text); } catch { throw new Error('Invalid response'); }

                if (data.status === 'success') {
                    overlay.innerHTML = '<span>✅ Synced successfully!</span>';
                    setTimeout(() => window.location.href = '{{ route('home') }}', 700);
                } else {
                    overlay.innerHTML = '<span>⚠️ Sync failed, redirecting...</span>';
                    setTimeout(() => window.location.href = '{{ route('home') }}', 700);
                }
            } catch (err) {
                console.error('Sync error:', err);
                overlay.innerHTML = '<span>⚠️ Error occurred, redirecting...</span>';
                setTimeout(() => window.location.href = '{{ route('home') }}', 700);
            }
        });
    });
    </script>

</body>
</html>
