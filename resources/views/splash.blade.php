<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GhostNet • Initializing</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    /* === Smooth Cinematic Animations === */
    @keyframes fadeInSmooth {
      0% { opacity: 0; transform: translateY(30px) scale(0.97); filter: blur(8px); }
      100% { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
    }
    @keyframes glowPulse {
      0%, 100% { text-shadow: 0 0 12px #dc2626, 0 0 24px #7f1d1d; }
      50% { text-shadow: 0 0 30px #ef4444, 0 0 50px #7f1d1d; }
    }
    @keyframes fogDrift {
      0% { transform: translateX(0) scale(1); opacity: 0.25; }
      50% { transform: translateX(-8%) scale(1.05); opacity: 0.5; }
      100% { transform: translateX(0) scale(1); opacity: 0.25; }
    }

    body {
      margin: 0;
      background: radial-gradient(circle at 40% 35%, #0b0b0b, #000 85%);
      color: white;
      font-family: 'Inter', sans-serif;
      overflow: hidden;
    }

    .fade-seq > * {
      opacity: 0;
      animation: fadeInSmooth 1.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }
    .fade-seq > *:nth-child(1) { animation-delay: 0.2s; }
    .fade-seq > *:nth-child(2) { animation-delay: 0.7s; }
    .fade-seq > *:nth-child(3) { animation-delay: 1.2s; }

    .glow { animation: glowPulse 4s ease-in-out infinite; }

    .fog-layer {
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 25% 50%, rgba(239,68,68,0.07), transparent 70%),
                  radial-gradient(circle at 70% 60%, rgba(239,68,68,0.05), transparent 80%);
      animation: fogDrift 18s ease-in-out infinite;
      filter: blur(90px);
      mix-blend-mode: screen;
      z-index: 0;
    }

    #startSync {
      transition: all 0.4s cubic-bezier(0.22, 1, 0.36, 1);
    }
    #startSync:hover {
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 0 30px #ef444470;
    }

    .orb {
      background: radial-gradient(circle at 40% 30%, rgba(239,68,68,0.4), rgba(0,0,0,0.6));
      box-shadow: 0 0 60px 10px rgba(239,68,68,0.25);
      border: 1px solid rgba(239,68,68,0.4);
      backdrop-filter: blur(6px);
    }

    canvas {
      position: absolute;
      inset: 0;
      z-index: 0;
    }

    #fadeOverlay {
      position: fixed;
      inset: 0;
      background: black;
      opacity: 0;
      pointer-events: none;
      transition: opacity 1.2s ease-in-out;
      z-index: 50;
    }
    #fadeOverlay.active {
      opacity: 1;
    }
  </style>
</head>

<body class="flex flex-col items-center justify-center h-screen relative">
  <canvas id="particles"></canvas>
  <div class="fog-layer"></div>

  <div class="z-10 flex flex-col items-center text-center fade-seq">
    {{-- 👻 Logo Orb --}}
    <div>
      <div class="w-32 h-32 flex items-center justify-center rounded-full orb">
        <span class="text-6xl glow">👻</span>
      </div>
    </div>

    {{-- 🩸 Title --}}
    <div>
      <h1 class="text-6xl font-extrabold glow mb-3 mt-6 tracking-wide">
        Ghost<span class="text-red-500">Net</span>
      </h1>
      <p class="text-gray-400 text-lg">Streaming from the shadows — syncing your universe ☁️</p>
    </div>

    {{-- 🚀 Start Button --}}
    <div>
      <button id="startSync"
        class="px-10 py-3 bg-gradient-to-r from-red-600 to-rose-700 rounded-full font-semibold text-lg shadow-[0_0_25px_#ef444440] focus:ring-4 focus:ring-red-600/40 focus:outline-none mt-10">
        Launch GhostNet
      </button>
      <div id="status" class="text-gray-400 text-base mt-6 h-6"></div>
    </div>
  </div>

  <div id="fadeOverlay"></div>

  <div class="absolute bottom-5 text-gray-600 text-sm tracking-wide z-10">
    © {{ date('Y') }} GhostNet • Stream Beyond Reality
  </div>

  <script>
    // === Smooth continuous ember particles ===
    const canvas = document.getElementById('particles');
    const ctx = canvas.getContext('2d');
    let particles = [];

    function resizeCanvas() {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    const particleCount = 80;
    for (let i = 0; i < particleCount; i++) {
      particles.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        r: Math.random() * 1.8 + 0.3,
        s: Math.random() * 25 + 15,
        o: Math.random() * 0.4 + 0.2,
        drift: Math.random() * 0.3 - 0.15
      });
    }

    function animate() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      for (const p of particles) {
        ctx.beginPath();
        ctx.fillStyle = `rgba(239,68,68,${p.o})`;
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fill();

        p.y += p.s * 0.016;
        p.x += p.drift;
        if (p.y > canvas.height + 10) p.y = -10;
        if (p.x > canvas.width + 10) p.x = -10;
        if (p.x < -10) p.x = canvas.width + 10;
      }
      requestAnimationFrame(animate);
    }
    requestAnimationFrame(animate);

    // === Sync logic (unchanged) ===
    document.getElementById('startSync').addEventListener('click', async () => {
      const status = document.getElementById('status');
      const button = document.getElementById('startSync');
      const fadeOverlay = document.getElementById('fadeOverlay');

      button.disabled = true;
      button.classList.add('opacity-60', 'cursor-wait');
      status.innerText = '🔄 Connecting to remote...';

      try {
        const res = await fetch('/sync?direction=to-local', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        });

        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch (e) { throw new Error('Invalid server response:\n' + text.substring(0, 200)); }

        if (data.status === 'success') {
          status.innerText = '✅ Sync complete!';
          setTimeout(() => fadeOverlay.classList.add('active'), 400);
          setTimeout(() => window.location.href = '/', 1300);
        } else {
          status.innerText = '❌ ' + (data.message || 'Sync failed.');
          button.disabled = false;
          button.classList.remove('opacity-60', 'cursor-wait');
        }
      } catch (err) {
        status.innerText = '💥 ' + err.message;
        button.disabled = false;
        button.classList.remove('opacity-60', 'cursor-wait');
      }
    });
  </script>
</body>
</html>
