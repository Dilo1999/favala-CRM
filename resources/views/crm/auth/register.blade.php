<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create account · Favala CRM</title>
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
        <div class="w-full max-w-sm relative" x-data="{ showPassword: false, showConfirm: false }">
            {{-- Mobile: bot above form --}}
            <div class="flex flex-col items-center gap-4 mb-8 lg:hidden">
                <div class="zaha-login-bubble">
                    Hi, I'm <span class="text-accent font-semibold">Fava</span> — let's get your Favala account set up.
                </div>
                <div class="relative h-80 w-80 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-accent/20 blur-2xl"></div>
                    <img src="{{ asset('images/zaha/bot-idle.svg') }}" alt="Fava" class="zaha-dynamic-bot animate-idle relative w-80 h-80 object-contain" />
                </div>
            </div>

            {{-- Desktop: bot anchored to the left of the centered form --}}
            <div class="hidden lg:flex flex-col items-center gap-3 absolute right-full top-1/2 -translate-y-1/2 mr-10">
                <div class="zaha-login-bubble">
                    Hi, I'm <span class="text-accent font-semibold">Fava</span> — let's get your Favala account set up.
                </div>
                <div class="relative h-[28rem] w-[28rem] flex items-center justify-center shrink-0">
                    <div class="absolute inset-0 rounded-full bg-accent/20 blur-2xl"></div>
                    <img src="{{ asset('images/zaha/bot-idle.svg') }}" alt="Fava" class="zaha-dynamic-bot animate-idle relative w-[28rem] h-[28rem] object-contain" />
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

                <h1 class="text-lg font-bold text-white">Create your account</h1>
                <p class="text-sm text-zinc-500 mt-1 mb-6">An admin will need to approve it before you can sign in.</p>

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-500/10 text-red-400 text-sm px-4 py-3 space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="crm-login-form space-y-5">
                    @csrf
                    <div>
                        <label class="crm-login-label" for="register-name">Full name</label>
                        <div class="crm-login-input-wrap">
                            <x-heroicon-o-user class="crm-login-input-icon" />
                            <input id="register-name" type="text" name="name" value="{{ old('name') }}" required autofocus
                                placeholder="Your full name"
                                class="crm-login-input" />
                        </div>
                    </div>
                    <div>
                        <label class="crm-login-label" for="register-email">Email</label>
                        <div class="crm-login-input-wrap">
                            <x-heroicon-o-mail class="crm-login-input-icon" />
                            <input id="register-email" type="email" name="email" value="{{ old('email') }}" required
                                placeholder="you@example.com"
                                class="crm-login-input" />
                        </div>
                    </div>
                    <div>
                        <label class="crm-login-label" for="register-password">Password</label>
                        <div class="crm-login-input-wrap">
                            <x-heroicon-o-lock-closed class="crm-login-input-icon" />
                            <input id="register-password" :type="showPassword ? 'text' : 'password'" name="password" required
                                placeholder="At least 8 characters"
                                class="crm-login-input crm-login-input--password" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300 transition-colors">
                                <x-heroicon-o-eye class="w-[1.125rem] h-[1.125rem]" x-show="!showPassword" />
                                <x-heroicon-o-eye-off class="w-[1.125rem] h-[1.125rem]" x-show="showPassword" x-cloak />
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="crm-login-label" for="register-password-confirm">Confirm password</label>
                        <div class="crm-login-input-wrap">
                            <x-heroicon-o-lock-closed class="crm-login-input-icon" />
                            <input id="register-password-confirm" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required
                                placeholder="Re-enter your password"
                                class="crm-login-input crm-login-input--password" />
                            <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300 transition-colors">
                                <x-heroicon-o-eye class="w-[1.125rem] h-[1.125rem]" x-show="!showConfirm" />
                                <x-heroicon-o-eye-off class="w-[1.125rem] h-[1.125rem]" x-show="showConfirm" x-cloak />
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="crm-login-submit">
                        <x-heroicon-o-user-add class="w-[1.125rem] h-[1.125rem]" />
                        Create account
                    </button>
                </form>

                <p class="text-center text-sm text-zinc-500 mt-6">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-medium text-accent hover:text-accent-light transition-colors">Sign in</a>
                </p>

                <div class="flex items-center gap-2 justify-center text-xs text-zinc-500 mt-4 pt-4 border-t border-white/10">
                    <x-heroicon-o-shield-check class="w-4 h-4 text-accent" />
                    New accounts require admin approval before first sign-in.
                </div>
            </div>

            <p class="text-center text-xs text-zinc-600 mt-6">&copy; {{ now()->year }} Favala. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
