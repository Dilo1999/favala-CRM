<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Dashboard' }} · Favala CRM</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-950 text-gray-100 antialiased">
    <div class="flex h-full">
        {{-- Sidebar --}}
        <aside class="w-16 shrink-0 bg-gray-900 border-r border-gray-800 flex flex-col items-center">
            <div class="h-16 flex items-center justify-center border-b border-gray-800 w-full">
                <div class="h-8 w-8 rounded-lg bg-orange-600 flex items-center justify-center font-bold text-white">F</div>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 space-y-1 w-full flex flex-col items-center">
                @php
                    $navItems = [
                        ['route' => 'crm.dashboard', 'label' => 'Dashboard', 'icon' => 'chart-bar'],
                        ['route' => 'crm.leads', 'label' => 'Leads', 'icon' => 'user-group'],
                        ['route' => 'crm.queries', 'label' => 'Queries', 'icon' => 'chat-alt-2'],
                        ['route' => 'crm.tasks', 'label' => 'Tasks', 'icon' => 'check-circle'],
                        ['route' => 'crm.activities', 'label' => 'Activities', 'icon' => 'clipboard-list'],
                        ['route' => 'crm.deals', 'label' => 'Deals', 'icon' => 'briefcase'],
                        ['route' => 'crm.quotations', 'label' => 'Quotations', 'icon' => 'document-text'],
                        ['route' => 'crm.invoices', 'label' => 'Invoices', 'icon' => 'currency-dollar'],
                        ['route' => 'crm.deliveries', 'label' => 'Deliveries', 'icon' => 'truck'],
                        ['route' => 'crm.returns', 'label' => 'Returns', 'icon' => 'receipt-refund'],
                        ['route' => 'crm.receipts', 'label' => 'Receipts', 'icon' => 'archive'],
                        ['route' => 'crm.products', 'label' => 'Products', 'icon' => 'cube'],
                        ['route' => 'crm.prices', 'label' => 'Price List', 'icon' => 'tag'],
                        ['route' => 'crm.vendors', 'label' => 'Vendors', 'icon' => 'office-building'],
                        ['route' => 'crm.targets', 'label' => 'Targets', 'icon' => 'flag'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route'].'*'); @endphp
                    <a href="{{ route($item['route']) }}"
                       title="{{ $item['label'] }}"
                       class="h-10 w-10 rounded-lg flex items-center justify-center transition
                              {{ $active ? 'bg-orange-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="w-5 h-5 shrink-0" />
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-gray-800 py-4 w-full flex flex-col items-center gap-3">
                <div class="flex flex-col-reverse -space-y-2 -space-y-reverse">
                    @foreach (\App\Models\User::crmStaff()->limit(5)->get() as $member)
                        <div title="{{ $member->name }}" class="h-7 w-7 rounded-full bg-orange-600/20 text-orange-400 text-xs font-semibold flex items-center justify-center ring-2 ring-gray-900">
                            {{ mb_substr($member->name, 0, 1) }}
                        </div>
                    @endforeach
                </div>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('crm.settings') }}"
                       title="Settings"
                       class="h-10 w-10 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->routeIs('crm.settings*') ? 'bg-orange-600 text-white' : '' }}">
                        <x-heroicon-o-cog class="w-5 h-5" />
                    </a>
                @endif
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 shrink-0 border-b border-gray-800 flex items-center justify-end px-6 gap-3">
                <a href="{{ route('crm.price-calculator') }}"
                   title="Price Calculator"
                   class="h-9 w-9 rounded-lg flex items-center justify-center transition
                          {{ request()->routeIs('crm.price-calculator*') ? 'bg-orange-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <x-heroicon-o-calculator class="w-5 h-5" />
                </a>
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role) }}</p>
                </div>
                <div class="h-9 w-9 rounded-full bg-orange-600/20 text-orange-400 flex items-center justify-center font-semibold">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-white" title="Log out">
                        <x-heroicon-o-logout class="w-5 h-5" />
                    </button>
                </form>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-emerald-500/10 text-emerald-400 text-sm px-4 py-3">{{ session('status') }}</div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
