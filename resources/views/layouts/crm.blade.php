<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Dashboard' }} · Favala CRM</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.lordicon.com/lordicon.js" defer></script>
</head>
<body class="crm-app h-full bg-zinc-900 text-zinc-100 antialiased">
    <div class="flex h-full">
        {{-- Sidebar --}}
        <aside class="w-16 shrink-0 bg-zinc-800 border-r border-white/10 flex flex-col items-center">
            <div class="h-16 flex items-center justify-center border-b border-white/10 w-full">
                <div class="h-8 w-8 rounded-lg bg-accent flex items-center justify-center font-bold text-white">F</div>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 space-y-1 w-full flex flex-col items-center">
                @php
                    $navItems = [
                        ['route' => 'crm.dashboard', 'label' => 'Dashboard', 'icon' => 'chart-bar'],
                        ['route' => 'crm.leads', 'label' => 'Leads', 'icon' => 'user-group'],
                        ['route' => 'crm.queries', 'label' => 'Queries', 'icon' => 'chat-alt-2'],
                        ['route' => 'crm.tasks', 'label' => 'Tasks', 'icon' => 'check-circle', 'anim' => 'draw'],
                        ['route' => 'crm.activities', 'label' => 'Activities', 'icon' => 'clipboard-list', 'anim' => 'bounce'],
                        ['route' => 'crm.deals', 'label' => 'Deals', 'icon' => 'briefcase', 'anim' => 'swing'],
                        ['route' => 'crm.quotations', 'label' => 'Quotations', 'icon' => 'document-text', 'anim' => 'draw'],
                        ['route' => 'crm.invoices', 'label' => 'Invoices', 'icon' => 'currency-dollar', 'anim' => 'pulse'],
                        ['route' => 'crm.deliveries', 'label' => 'Deliveries', 'icon' => 'truck', 'anim' => 'drive'],
                        ['route' => 'crm.returns', 'label' => 'Returns', 'icon' => 'receipt-refund', 'anim' => 'spin'],
                        ['route' => 'crm.receipts', 'label' => 'Receipts', 'icon' => 'archive', 'anim' => 'archive'],
                        ['route' => 'crm.products', 'label' => 'Products', 'icon' => 'cube', 'anim' => 'cube'],
                        ['route' => 'crm.prices', 'label' => 'Price List', 'icon' => 'tag', 'anim' => 'tag'],
                        ['route' => 'crm.vendors', 'label' => 'Vendors', 'icon' => 'office-building', 'anim' => 'rise'],
                        ['route' => 'crm.targets', 'label' => 'Targets', 'icon' => 'flag', 'anim' => 'wave'],
                    ];

                    // Verified Lordicon codes (confirmed via a public curated reference) for the
                    // items with an unambiguous animated match. Everything else keeps the
                    // CSS-animated Heroicon set rather than guessing at a mismatched icon.
                    $lordIcons = [
                        'crm.dashboard' => 'lupuorrc', // trending-up / chart
                        'crm.leads' => 'cnyeuzxc',     // call phone
                        'crm.queries' => 'uvextprq',   // chat
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route'].'*'); @endphp
                    <a href="{{ route($item['route']) }}"
                       title="{{ $item['label'] }}"
                       class="crm-nav-item {{ $active ? 'is-active' : '' }} h-10 w-10 rounded-lg flex items-center justify-center transition-colors duration-200
                              {{ $active ? 'bg-accent text-white' : 'text-zinc-400 hover:bg-zinc-700 hover:text-white' }}">
                        @if (isset($lordIcons[$item['route']]))
                            <lord-icon class="crm-nav-icon" src="https://cdn.lordicon.com/{{ $lordIcons[$item['route']] }}.json"
                                trigger="hover" stroke="90" colors="primary:#a1a1aa,secondary:#e07a5f"
                                style="width:24px;height:24px"></lord-icon>
                        @else
                            <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="crm-nav-icon anim-{{ $item['anim'] ?? 'bounce' }} w-6 h-6 shrink-0" />
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 py-4 w-full flex flex-col items-center gap-3">
                @if (auth()->user()->isAdmin())
                    @php $settingsActive = request()->routeIs('crm.settings*'); @endphp
                    <a href="{{ route('crm.settings') }}"
                       title="Settings"
                       class="crm-nav-item {{ $settingsActive ? 'is-active' : '' }} h-10 w-10 rounded-lg flex items-center justify-center transition-colors duration-200 {{ $settingsActive ? 'bg-accent text-white' : 'text-zinc-400 hover:bg-zinc-700 hover:text-white' }}">
                        <lord-icon class="crm-nav-icon" src="https://cdn.lordicon.com/ryyjawhw.json"
                            trigger="hover" stroke="90" colors="primary:#a1a1aa,secondary:#e07a5f"
                            style="width:24px;height:24px"></lord-icon>
                    </a>
                @endif
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 shrink-0 border-b border-white/10 flex items-center justify-end px-6 gap-3">
                @php $calcActive = request()->routeIs('crm.price-calculator*'); @endphp
                <a href="{{ route('crm.price-calculator') }}"
                   title="Price Calculator"
                   class="crm-nav-item {{ $calcActive ? 'is-active' : '' }} h-9 w-9 rounded-lg flex items-center justify-center transition-colors duration-200
                          {{ $calcActive ? 'bg-accent text-white' : 'text-zinc-400 hover:bg-zinc-700 hover:text-white' }}">
                    <x-heroicon-o-calculator class="crm-nav-icon anim-pulse w-6 h-6" />
                </a>
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-zinc-500">{{ ucfirst(auth()->user()->role) }}</p>
                </div>
                <div class="h-9 w-9 rounded-full bg-accent/20 text-accent flex items-center justify-center font-semibold">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-zinc-400 hover:text-white" title="Log out">
                        <x-heroicon-o-logout class="w-5 h-5" />
                    </button>
                </form>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-green-500/10 text-green-400 text-sm px-4 py-3">{{ session('status') }}</div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
