<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Shop Catalog' }}</title>
    <link rel="icon" href="{{ asset('images/logo/Favala-1.png') }}" type="image/png" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
{{--
    Deliberately NOT the CRM's sidebar shell (layouts.crm). Shop Catalog is a
    separate support system — its own database (see App\Models\ShopCatalog) — and
    is reached from the CRM via a single outbound link, not folded into the CRM's
    own navigation. This top-nav shell is what makes it read as its own system
    once you're in it, rather than another CRM section. Runs a light theme
    (unlike the CRM's fixed dark theme) per explicit request.
--}}
<body class="crm-app h-full bg-slate-50 text-slate-900 antialiased">
    <div class="flex flex-col h-full">
        <header class="shrink-0 border-b border-slate-200 bg-white/90 backdrop-blur sticky top-0 z-10">
            <div class="flex items-center justify-between h-16 px-8 max-w-6xl mx-auto w-full">
                <div class="flex items-center gap-8">
                    <span class="text-base font-semibold text-slate-900 tracking-tight flex items-center gap-2">
                        <span class="h-7 w-7 rounded-lg bg-accent-light/20 text-accent flex items-center justify-center">
                            <x-heroicon-o-shopping-bag class="w-4 h-4" />
                        </span>
                        Shop Catalog
                    </span>
                    <nav class="flex items-center gap-1">
                        @foreach ([
                            ['route' => 'shop-catalog.shops', 'label' => 'Shops'],
                            ['route' => 'shop-catalog.products', 'label' => 'Products'],
                        ] as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150 {{ $active ? 'text-accent bg-accent-light/15' : 'text-slate-500 hover:text-slate-900' }}">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="flex items-center gap-5">
                    <a href="{{ route('crm.dashboard') }}" class="text-sm text-slate-500 hover:text-slate-900 flex items-center gap-1.5 transition-colors">
                        <x-heroicon-o-arrow-left class="w-3.5 h-3.5" /> Back to CRM
                    </a>
                    <div class="h-4 w-px bg-slate-200"></div>
                    <div class="flex items-center gap-2.5">
                        <div class="h-7 w-7 rounded-full bg-accent-light/20 text-accent flex items-center justify-center text-xs font-semibold">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <p class="text-sm text-slate-600 hidden sm:block">{{ auth()->user()->name }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-slate-900 transition-colors" title="Log out">
                            <x-heroicon-o-logout class="w-4 h-4" />
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto">
            <div class="max-w-6xl mx-auto w-full px-8 py-8">
                @if (session('status'))
                    <div class="mb-6 rounded-lg bg-accent-light/15 border border-accent/20 text-accent-hover text-sm px-4 py-3">{{ session('status') }}</div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
