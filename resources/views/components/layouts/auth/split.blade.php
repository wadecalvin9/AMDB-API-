<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <style>
        :root {
            --accent: #ff004c;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            background-color: #0b0b0b;
        }

        .auth-container {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            height: 100vh;
            width: 100vw;
        }

        /* === LEFT SIDE — Cinematic Carousel === */
        .carousel {
            position: relative;
            overflow: hidden;
            background-color: #000;
        }

        .carousel img {
            position: absolute;
            top: 50%;
            left: 50%;
            width: auto;
            height: 100%;
            object-fit: cover;
            transform: translate(-50%, -50%) scale(1);
            opacity: 0;
            transition: opacity 2s ease-in-out, transform 15s ease-in-out;
        }

        .carousel img.active {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.1);
            z-index: 1;
        }

        @media (min-aspect-ratio: 16/9) {
            .carousel img {
                width: 100%;
                height: auto;
            }
        }

        .carousel-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top right, rgba(0,0,0,0.85), rgba(0,0,0,0.4));
            z-index: 2;
        }

        .quote-box {
            position: absolute;
            bottom: 5rem;
            left: 4rem;
            z-index: 3;
            max-width: 70%;
            color: #fff;
            text-shadow: 0 3px 8px rgba(0, 0, 0, 0.8);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 1.2s ease, transform 1.2s ease;
        }

        .quote-box.active {
            opacity: 1;
            transform: translateY(0);
        }

        .quote-text {
            font-size: 1.9rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .quote-author {
            margin-top: 0.75rem;
            font-size: 1rem;
            opacity: 0.85;
        }

        /* === RIGHT SIDE — Auth Form === */
        .auth-form {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            background: radial-gradient(circle at top right, #121212, #0a0a0a);
            height: 100%;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
        }

        .auth-form::-webkit-scrollbar {
            width: 6px;
        }

        .auth-form::-webkit-scrollbar-thumb {
            background-color: rgba(255,255,255,0.15);
            border-radius: 10px;
        }

        .form-card {
            width: 460px;
            max-width: 95%;
            padding: 3.5rem 2.5rem;
            margin-top: 6rem;
            margin-bottom: 4rem;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 40px rgba(0,0,0,0.5);
            backdrop-filter: blur(20px);
            text-align: center;
        }

        .app-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 1.4rem;
            color: var(--accent);
            margin-bottom: 1.5rem;
        }

        .app-logo i {
            font-size: 1.6rem;
        }

        .auth-toggle {
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #aaa;
        }

        .auth-toggle a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-toggle a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            font-size: 0.75rem;
            color: #777;
            padding-bottom: 1rem;
        }

        /* === MOBILE === */
        @media (max-width: 1024px) {
            .auth-container {
                grid-template-columns: 1fr;
            }

            .carousel {
                display: none;
            }

            .form-card {
                width: 100%;
                max-width: 420px;
                padding: 2rem;
                margin-top: 3rem;
            }

            .auth-form {
                overflow-y: auto;
                padding-bottom: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <!-- LEFT SIDE — Cinematic Carousel -->
        <div class="carousel">
            <div class="carousel-overlay"></div>

            <img src="https://i.pinimg.com/1200x/f5/c6/90/f5c690e5037c51f1c5d75b70775130f9.jpg" class="active" alt="Background 1">
            <img src="https://i.pinimg.com/1200x/00/93/45/009345c6dd971bb19142fa523369a072.jpg" alt="Background 2">
            <img src="https://i.pinimg.com/736x/12/4c/dd/124cdd849ceaeb8c338caee0330a456a.jpg" alt="Background 3">
            <img src="https://i.pinimg.com/736x/ab/9c/8e/ab9c8e8048fec1b54ef1fdcb27c37bae.jpg" alt="Background 4">
            <img src="https://i.pinimg.com/736x/40/f2/0f/40f20f49be7345c7221a4cc56fefc830.jpg" alt="Background 5">
            <img src="https://i.pinimg.com/736x/95/d8/76/95d8761e1204cb8b4daf0bd90daad11e.jpg" alt="Background 6">

            @php
                [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
            @endphp

            <div class="quote-box active" id="quoteBox">
                <p class="quote-text" id="quoteText">&ldquo;{{ trim($message) }}&rdquo;</p>
                <p class="quote-author" id="quoteAuthor">— {{ trim($author) }}</p>
            </div>
        </div>

        <!-- RIGHT SIDE — Auth Form -->
        <div class="auth-form">
            <div class="form-card">
                <a href="{{ route('home') }}" class="app-logo">
                    <i class="fas fa-play-circle"></i>
                    {{ config('app.name', 'Stream') }}
                </a>

                <!-- Login/Register form slot -->
                {{ $slot }}

                <!-- Toggle links -->

            </div>

            <div class="footer">
                &copy; {{ now()->year }} {{ config('app.name', 'Stream') }} — All rights reserved.
            </div>
        </div>
    </div>

    @fluxScripts

    <script>
        const slides = document.querySelectorAll('.carousel img');
        const quoteBox = document.getElementById('quoteBox');
        const quoteText = document.getElementById('quoteText');
        const quoteAuthor = document.getElementById('quoteAuthor');

        let current = 0;
        const quotes = [
            ["The future belongs to those who believe in the beauty of their dreams.", "Eleanor Roosevelt"],
            ["Cinema is a matter of what's in the frame and what's out.", "Martin Scorsese"],
            ["The way to get started is to quit talking and begin doing.", "Walt Disney"],
            ["Every story deserves to be told beautifully.", "Unknown"],
            ["Dream big, stream bigger.", "Stream"]
        ];

        function changeSlide() {
            // Fade out current
            slides[current].classList.remove('active');
            quoteBox.classList.remove('active');

            // Change image + quote
            current = (current + 1) % slides.length;
            const [text, author] = quotes[current % quotes.length];

            // Update quote text
            setTimeout(() => {
                quoteText.innerHTML = `&ldquo;${text}&rdquo;`;
                quoteAuthor.textContent = `— ${author}`;
                slides[current].classList.add('active');
                quoteBox.classList.add('active');
            }, 1000);
        }

        setInterval(changeSlide, 6000);
    </script>
</body>
</html>
