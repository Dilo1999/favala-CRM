<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign in · Favala CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="crm-app h-full bg-zinc-900 text-zinc-100 antialiased flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="flex items-center gap-2 justify-center mb-8">
            <div class="h-10 w-10 rounded-lg bg-accent flex items-center justify-center font-bold text-white text-lg">F</div>
            <span class="font-bold tracking-wide text-xl text-white">FAVALA CRM</span>
        </div>

        <div class="bg-zinc-800 border border-white/10 rounded-xl p-6">
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-500/10 text-red-400 text-sm px-4 py-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-zinc-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm focus:border-accent focus:ring-accent" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-300 mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm focus:border-accent focus:ring-accent" />
                </div>
                <label class="flex items-center gap-2 text-sm text-zinc-400">
                    <input type="checkbox" name="remember" class="rounded border-white/10 bg-zinc-700 text-accent" />
                    Remember me
                </label>
                <button type="submit" class="w-full bg-accent hover:bg-accent-hover text-white text-sm font-semibold rounded-lg py-2.5">
                    Sign in
                </button>
            </form>
        </div>
    </div>
</body>
</html>
