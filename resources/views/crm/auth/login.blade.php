<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign in · Favala CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-950 text-gray-100 antialiased flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="flex items-center gap-2 justify-center mb-8">
            <div class="h-10 w-10 rounded-lg bg-orange-600 flex items-center justify-center font-bold text-white text-lg">F</div>
            <span class="font-bold tracking-wide text-xl text-white">FAVALA CRM</span>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-500/10 text-red-400 text-sm px-4 py-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm focus:border-orange-500 focus:ring-orange-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm focus:border-orange-500 focus:ring-orange-500" />
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-400">
                    <input type="checkbox" name="remember" class="rounded border-gray-700 bg-gray-800 text-orange-600" />
                    Remember me
                </label>
                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold rounded-lg py-2.5">
                    Sign in
                </button>
            </form>
        </div>
    </div>
</body>
</html>
