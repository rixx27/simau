<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            .auth-accent-lines {
                position: fixed;
                inset: 0;
                pointer-events: none;
                opacity: .7;
            }
            .auth-hline,
            .auth-vline {
                position: absolute;
                background: #27272a;
                will-change: transform, opacity;
            }
            .auth-hline {
                left: 0;
                right: 0;
                height: 1px;
                transform: scaleX(0);
                transform-origin: 50% 50%;
                animation: authDrawX .8s cubic-bezier(.22, .61, .36, 1) forwards;
            }
            .auth-vline {
                top: 0;
                bottom: 0;
                width: 1px;
                transform: scaleY(0);
                transform-origin: 50% 0%;
                animation: authDrawY .9s cubic-bezier(.22, .61, .36, 1) forwards;
            }
            .auth-hline:nth-child(1) { top: 18%; animation-delay: .12s; }
            .auth-hline:nth-child(2) { top: 50%; animation-delay: .22s; }
            .auth-hline:nth-child(3) { top: 82%; animation-delay: .32s; }
            .auth-vline:nth-child(4) { left: 22%; animation-delay: .42s; }
            .auth-vline:nth-child(5) { left: 50%; animation-delay: .54s; }
            .auth-vline:nth-child(6) { left: 78%; animation-delay: .66s; }
            .auth-hline::after,
            .auth-vline::after {
                content: "";
                position: absolute;
                inset: 0;
                background: linear-gradient(90deg, transparent, rgba(250, 250, 250, .24), transparent);
                opacity: 0;
                animation: authShimmer .9s ease-out forwards;
            }
            .auth-hline:nth-child(1)::after { animation-delay: .12s; }
            .auth-hline:nth-child(2)::after { animation-delay: .22s; }
            .auth-hline:nth-child(3)::after { animation-delay: .32s; }
            .auth-vline:nth-child(4)::after { animation-delay: .42s; }
            .auth-vline:nth-child(5)::after { animation-delay: .54s; }
            .auth-vline:nth-child(6)::after { animation-delay: .66s; }
            @keyframes authDrawX {
                0% { transform: scaleX(0); opacity: 0; }
                60% { opacity: .95; }
                100% { transform: scaleX(1); opacity: .7; }
            }
            @keyframes authDrawY {
                0% { transform: scaleY(0); opacity: 0; }
                60% { opacity: .95; }
                100% { transform: scaleY(1); opacity: .7; }
            }
            @keyframes authShimmer {
                0% { opacity: 0; }
                35% { opacity: .25; }
                100% { opacity: 0; }
            }
            .auth-card-animate {
                opacity: 0;
                transform: translateY(20px);
                animation: authFadeUp .8s cubic-bezier(.22, .61, .36, 1) .25s forwards;
            }
            @keyframes authFadeUp {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            @media (prefers-reduced-motion: reduce) {
                .auth-hline,
                .auth-vline,
                .auth-hline::after,
                .auth-vline::after,
                .auth-card-animate {
                    animation: none !important;
                    opacity: 1 !important;
                    transform: none !important;
                }
            }
        </style>
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-50 antialiased selection:bg-zinc-50 selection:text-zinc-950">
        <div class="relative min-h-screen flex flex-col overflow-x-hidden">
            <!-- Subtle vignette -->
            <div class="fixed inset-0 pointer-events-none [background:radial-gradient(80%_60%_at_50%_30%,rgba(255,255,255,0.06),transparent_60%)]"></div>

            <!-- Animated accent grid lines -->
            <div class="auth-accent-lines" aria-hidden="true">
                <div class="auth-hline"></div>
                <div class="auth-hline"></div>
                <div class="auth-hline"></div>
                <div class="auth-vline"></div>
                <div class="auth-vline"></div>
                <div class="auth-vline"></div>
            </div>

            <!-- Floating particles canvas -->
            <canvas
                id="auth-particles-canvas"
                aria-hidden="true"
                class="fixed inset-0 w-full h-full opacity-50 mix-blend-screen pointer-events-none"
            ></canvas>

            <!-- Top Header -->
            <header class="relative z-10 flex items-center justify-between px-6 py-4 border-b border-zinc-800/80 bg-zinc-950/60 backdrop-blur-sm">
                <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2.5 group">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="SIMAU"
                        width="28"
                        height="28"
                        class="w-7 h-7 rounded-lg object-contain"
                    />
                    <span class="text-xs font-semibold tracking-[0.14em] uppercase text-zinc-300 group-hover:text-zinc-50 transition-colors">
                        SIMAU
                    </span>
                </a>

                @if (request()->routeIs('register'))
                    <a
                        href="{{ route('login') }}"
                        wire:navigate
                        class="inline-flex items-center gap-2 h-9 px-3.5 rounded-lg border border-zinc-800 bg-zinc-900 text-xs font-medium text-zinc-100 hover:bg-zinc-800 transition-colors"
                    >
                        <span>Masuk</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                @elseif (Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        wire:navigate
                        class="inline-flex items-center gap-2 h-9 px-3.5 rounded-lg border border-zinc-800 bg-zinc-900 text-xs font-medium text-zinc-100 hover:bg-zinc-800 transition-colors"
                    >
                        <span>Daftar Akun</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                @endif
            </header>

            <!-- Centered Card -->
            <main class="relative z-10 flex-1 grid place-items-center px-4 py-10">
                <div class="auth-card-animate w-full max-w-sm rounded-xl border border-zinc-800 bg-zinc-900/75 backdrop-blur-md shadow-2xl shadow-black/60 p-6">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts

        <script>
            (() => {
                if (window.__simauAuthParticlesInit) return;
                window.__simauAuthParticlesInit = true;

                let cleanup = null;

                const mountParticles = () => {
                    if (cleanup) {
                        cleanup();
                        cleanup = null;
                    }

                    const canvas = document.getElementById('auth-particles-canvas');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    if (!ctx) return;

                    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    let particles = [];
                    let rafId = 0;

                    const setSize = () => {
                        canvas.width = window.innerWidth;
                        canvas.height = window.innerHeight;
                    };

                    const createParticle = () => ({
                        x: Math.random() * canvas.width,
                        y: Math.random() * canvas.height,
                        v: Math.random() * 0.25 + 0.05,
                        o: Math.random() * 0.35 + 0.15,
                    });

                    const initParticles = () => {
                        particles = [];
                        const count = Math.floor((canvas.width * canvas.height) / 9000);
                        for (let i = 0; i < count; i++) {
                            particles.push(createParticle());
                        }
                    };

                    const drawFrame = () => {
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        for (let i = 0; i < particles.length; i++) {
                            const p = particles[i];
                            if (!prefersReduced) {
                                p.y -= p.v;
                                if (p.y < 0) {
                                    p.x = Math.random() * canvas.width;
                                    p.y = canvas.height + Math.random() * 40;
                                    p.v = Math.random() * 0.25 + 0.05;
                                    p.o = Math.random() * 0.35 + 0.15;
                                }
                            }
                            ctx.fillStyle = `rgba(250, 250, 250, ${p.o})`;
                            ctx.fillRect(p.x, p.y, 0.7, 2.2);
                        }
                        if (!prefersReduced) {
                            rafId = requestAnimationFrame(drawFrame);
                        }
                    };

                    const onResize = () => {
                        setSize();
                        initParticles();
                        if (prefersReduced) {
                            drawFrame();
                        }
                    };

                    setSize();
                    initParticles();
                    drawFrame();
                    window.addEventListener('resize', onResize);

                    cleanup = () => {
                        window.removeEventListener('resize', onResize);
                        if (rafId) cancelAnimationFrame(rafId);
                    };
                };

                document.addEventListener('DOMContentLoaded', mountParticles);
                document.addEventListener('livewire:navigated', mountParticles);
                document.addEventListener('livewire:navigating', () => {
                    if (cleanup) {
                        cleanup();
                        cleanup = null;
                    }
                });
            })();
        </script>
    </body>
</html>
