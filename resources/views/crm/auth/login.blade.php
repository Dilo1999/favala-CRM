<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign in · Favala CRM</title>
    <link rel="icon" href="{{ asset('images/logo/Favala-1.png') }}" type="image/png" />
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
                    Hi, I'm <span class="text-accent font-semibold">Fava</span> - your assistant here at Favala. Sign in to get started.
                </div>
                <div class="relative h-80 w-80 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-accent/20 blur-2xl"></div>
                    <img src="{{ asset('images/bot/bot-idle.svg') }}" alt="Fava" class="zaha-dynamic-bot animate-idle relative w-80 h-80 object-contain" />
                </div>
            </div>

            {{-- Desktop: bot anchored to the left of the centered form --}}
            <div class="hidden lg:flex flex-col items-center gap-3 absolute right-full top-1/2 -translate-y-1/2 mr-10">
                <div class="zaha-login-bubble">
                    Hi, I'm <span class="text-accent font-semibold">Fava</span> - your assistant here at Favala. Sign in to get started.
                </div>
                <div class="relative h-[28rem] w-[28rem] flex items-center justify-center shrink-0">
                    <div class="absolute inset-0 rounded-full bg-accent/20 blur-2xl"></div>
                    <img src="{{ asset('images/bot/bot-idle.svg') }}" alt="Fava" class="zaha-dynamic-bot animate-idle relative w-[28rem] h-[28rem] object-contain" />
                </div>
            </div>

            <div class="flex flex-col items-center gap-2 mb-6 lg:hidden">
                <x-favala-logo variant="hero" />
                <p class="text-xs text-zinc-500">Construction &amp; Hardware Supply — Maldives</p>
            </div>

            <div class="bg-zinc-800/90 backdrop-blur border border-white/10 rounded-2xl shadow-2xl shadow-black/40 p-6">
                <div class="hidden lg:flex flex-col items-center text-center gap-1 mb-6">
                    <x-favala-logo variant="auth" class="mx-auto object-center" />
                    <p class="text-xs text-zinc-500 leading-tight">Construction &amp; Hardware Supply</p>
                </div>

                <h1 class="text-lg font-bold text-white">Welcome Back</h1>
                <p class="text-sm text-zinc-500 mt-1 mb-6">Sign in to access your dashboard.</p>

                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-emerald-500/10 text-emerald-400 text-sm px-4 py-3">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-500/10 text-red-400 text-sm px-4 py-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="crm-login-form space-y-5">
                    @csrf
                    <div>
                        <label class="crm-login-label" for="login-email">Email</label>
                        <div class="crm-login-input-wrap">
                            <x-heroicon-o-user class="crm-login-input-icon" />
                            <input id="login-email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                placeholder="Enter your email"
                                class="crm-login-input" />
                        </div>
                    </div>
                    <div>
                        <label class="crm-login-label" for="login-password">Password</label>
                        <div class="crm-login-input-wrap">
                            <x-heroicon-o-lock-closed class="crm-login-input-icon" />
                            <input id="login-password" :type="showPassword ? 'text' : 'password'" name="password" required
                                placeholder="Enter your password"
                                class="crm-login-input crm-login-input--password" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300 transition-colors">
                                <x-heroicon-o-eye class="w-[1.125rem] h-[1.125rem]" x-show="!showPassword" />
                                <x-heroicon-o-eye-off class="w-[1.125rem] h-[1.125rem]" x-show="showPassword" x-cloak />
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-3 pt-0.5">
                        <label class="flex items-center gap-2.5 text-sm text-zinc-400 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="crm-login-checkbox rounded border-white/15 bg-zinc-900/80 text-accent focus:ring-accent/30" />
                            Remember me
                        </label>
                        <a href="#" class="text-sm font-medium text-accent hover:text-accent-light transition-colors">Forgot password?</a>
                    </div>
                    <button type="submit" class="crm-login-submit">
                        <x-heroicon-o-login class="w-[1.125rem] h-[1.125rem]" />
                        Sign In
                    </button>
                </form>

                <div class="flex items-center gap-2 justify-center text-xs text-zinc-500 mt-4 pt-4 border-t border-white/10">
                    <x-heroicon-o-shield-check class="w-4 h-4 text-accent" />
                    Your sign-in is protected with encrypted authentication.
                </div>
            </div>

            <p class="text-center text-xs text-zinc-600 mt-6">&copy; {{ now()->year }} Favala. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
