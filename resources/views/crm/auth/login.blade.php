<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign in · Favala CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="crm-app h-full bg-zinc-900 text-zinc-100 antialiased relative overflow-hidden">
    {{-- Decorative background glow --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-32 -left-24 h-80 w-80 rounded-full bg-accent/20 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-24 h-96 w-96 rounded-full bg-accent/10 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.06)_1px,transparent_0)] bg-[length:28px_28px]"></div>
    </div>

    <div class="relative min-h-full flex items-center justify-center px-4 py-12">
        {{-- Sign-in card — centered; Fava sits to the left on desktop --}}
        <div class="w-full max-w-sm relative" x-data="{ showPassword: false }">
            {{-- Mobile: bot above form --}}
            <div class="flex flex-col items-center gap-4 mb-8 lg:hidden">
                <div class="zaha-login-bubble">
                    Hi, I'm <span class="text-accent font-semibold">Fava</span> — your assistant here at Favala. Sign in to get started.
                </div>
                <div class="relative h-64 w-64 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-accent/20 blur-2xl"></div>
                    <img src="{{ asset('images/zaha/bot-idle.svg') }}" alt="Fava" class="zaha-dynamic-bot animate-idle relative w-64 h-64 object-contain" />
                </div>
            </div>

            {{-- Desktop: bot anchored to the left of the centered form --}}
            <div class="hidden lg:flex flex-col items-center gap-3 absolute right-full top-1/2 -translate-y-1/2 mr-5">
                <div class="zaha-login-bubble">
                    Hi, I'm <span class="text-accent font-semibold">Fava</span> — your assistant here at Favala. Sign in to get started.
                </div>
                <div class="relative h-80 w-80 flex items-center justify-center shrink-0">
                    <div class="absolute inset-0 rounded-full bg-accent/20 blur-2xl"></div>
                    <img src="{{ asset('images/zaha/bot-idle.svg') }}" alt="Fava" class="zaha-dynamic-bot animate-idle relative w-80 h-80 object-contain" />
                </div>
            </div>

            <div class="flex flex-col items-center gap-1 mb-6 lg:hidden">
                <span class="font-bold tracking-wide text-xl text-white">FAVALA CRM</span>
                <p class="text-xs text-zinc-500">Construction &amp; Hardware Supply — Maldives</p>
            </div>

            <div class="bg-zinc-800/90 backdrop-blur border border-white/10 rounded-2xl shadow-2xl shadow-black/40 p-6">
                <div class="hidden lg:flex items-center gap-2 mb-6">
                    <div class="h-9 w-9 rounded-lg bg-accent flex items-center justify-center font-bold text-white">F</div>
                    <div>
                        <p class="font-bold tracking-wide text-sm text-white leading-tight">FAVALA CRM</p>
                        <p class="text-xs text-zinc-500 leading-tight">Construction &amp; Hardware Supply</p>
                    </div>
                </div>

                <h1 class="text-lg font-bold text-white">Welcome Back</h1>
                <p class="text-sm text-zinc-500 mt-1 mb-6">Sign in to access your dashboard.</p>

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-500/10 text-red-400 text-sm px-4 py-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Email</label>
                        <div class="relative">
                            <x-heroicon-o-mail class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full pl-9 rounded-lg bg-zinc-900 border-white/10 text-white text-sm focus:border-accent focus:ring-accent" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Password</label>
                        <div class="relative">
                            <x-heroicon-o-lock-closed class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input :type="showPassword ? 'text' : 'password'" name="password" required
                                class="w-full pl-9 pr-9 rounded-lg bg-zinc-900 border-white/10 text-white text-sm focus:border-accent focus:ring-accent" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300">
                                <x-heroicon-o-eye class="w-4 h-4" x-show="!showPassword" />
                                <x-heroicon-o-eye-off class="w-4 h-4" x-show="showPassword" x-cloak />
                            </button>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-zinc-400">
                        <input type="checkbox" name="remember" class="rounded border-white/10 bg-zinc-900 text-accent" />
                        Remember me
                    </label>
                    <button type="submit" class="w-full flex items-center justify-center gap-2 bg-accent hover:bg-accent-hover text-white text-sm font-semibold rounded-lg py-2.5 transition-colors">
                        <x-heroicon-o-login class="w-4 h-4" /> Sign in
                    </button>
                </form>

                <div class="flex items-center gap-2 justify-center text-xs text-zinc-500 mt-6 pt-4 border-t border-white/10">
                    <x-heroicon-o-shield-check class="w-4 h-4 text-accent" />
                    Your sign-in is protected with encrypted authentication.
                </div>
            </div>

            <p class="text-center text-xs text-zinc-600 mt-6">&copy; {{ now()->year }} Favala. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
