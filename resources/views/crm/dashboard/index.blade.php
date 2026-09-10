<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Dashboard</h1>

        <div class="flex rounded-lg border border-white/10 w-fit overflow-hidden text-sm">
            @foreach (['overview' => 'Overview', 'analytics' => 'Analytics', 'staff' => 'Staff Performance'] as $key => $label)
                <button wire:click="$set('tab', '{{ $key }}')"
                    class="px-4 py-2 font-medium {{ $tab === $key ? 'bg-accent text-white' : 'text-zinc-400 hover:text-white' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if ($tab === 'overview')
        @php($o = $this->overview)
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 lg:items-stretch">
            <div class="lg:w-1/3 shrink-0 rounded-xl bg-zinc-800 border border-white/10 flex flex-col lg:min-h-[calc(100vh-12rem)]"
                x-data="{ mood: 'idle', getCurrentSvg() { return 'bot-' + this.mood + '.svg'; }, getMoodCategory() { return this.mood; } }">
                <div class="flex-1 flex flex-col items-center justify-start text-center p-6 pt-8">
                    <div class="w-64 -translate-x-2 flex flex-col items-center">
                    <div class="relative zaha-bot-hover mb-4 w-64">
                        <div class="absolute inset-0 rounded-full bg-accent/20 blur-2xl"></div>
                        <span class="zaha-bot-sparkle zaha-bot-sparkle--1" aria-hidden="true">✨</span>
                        <span class="zaha-bot-sparkle zaha-bot-sparkle--2" aria-hidden="true">⭐</span>
                        <span class="zaha-bot-sparkle zaha-bot-sparkle--3" aria-hidden="true">✨</span>
                        <span class="zaha-bot-sparkle zaha-bot-sparkle--4" aria-hidden="true">⭐</span>
                        {{-- <object>, not <img>: an <img>-embedded SVG can't receive mouse
                             hover internally, so the head/hand parts inside bot-idle.svg
                             (its own :hover rules) would never fire. --}}
                        <object type="image/svg+xml" :data="'{{ asset('images/zaha') }}/' + getCurrentSvg()" :class="'animate-' + getMoodCategory()"
                            aria-label="Fava" class="zaha-dynamic-bot w-64 h-64 object-contain animate-idle" data="{{ asset('images/zaha/bot-idle.svg') }}"></object>
                        <div class="zaha-speech-bubble" aria-hidden="true">
                            <span class="zaha-speech-bubble__spark" aria-hidden="true">✨</span>
                            <span class="zaha-speech-bubble__text">I love to help you!</span>
                        </div>
                    </div>
                    <h3 class="text-white font-bold text-lg">Hey, {{ explode(' ', auth()->user()->name)[0] }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">How can I help you today?</p>
                    </div>
                </div>

                <form class="flex items-center gap-2 p-3 border-t border-white/10">
                    <input type="text" placeholder="Message Fava…" class="flex-1 rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    <button type="submit" class="h-9 w-9 shrink-0 flex items-center justify-center rounded-lg bg-accent hover:bg-accent-hover text-white">
                        <x-heroicon-o-paper-airplane class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <div class="hidden lg:block w-px bg-white/10"></div>

            <div class="flex-1 flex flex-col gap-6 lg:min-h-[calc(100vh-12rem)]">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 shrink-0">
                    <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-zinc-400">Total Revenue</p>
                            <x-heroicon-o-currency-dollar class="w-5 h-5 text-accent" />
                        </div>
                        <p class="text-2xl font-bold text-white mt-2">MVR {{ number_format($o['total_revenue'], 2) }}</p>
                        <p class="text-xs text-zinc-500 mt-1">Based on all invoices</p>
                    </div>
                    <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-zinc-400">Customers</p>
                            <x-heroicon-o-user-group class="w-5 h-5 text-accent" />
                        </div>
                        <p class="text-2xl font-bold text-white mt-2">{{ number_format($o['customers']) }}</p>
                        <p class="text-xs text-zinc-500 mt-1">Total customers in the system</p>
                    </div>
                    <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-zinc-400">Hot Deals</p>
                            <x-heroicon-o-fire class="w-5 h-5 text-accent" />
                        </div>
                        <p class="text-2xl font-bold text-white mt-2">{{ $o['hot_deals'] }}</p>
                        <p class="text-xs text-zinc-500 mt-1">Deals currently marked as 'Hot'</p>
                    </div>
                    <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-zinc-400">Total Queries</p>
                            <x-heroicon-o-chat-alt-2 class="w-5 h-5 text-accent" />
                        </div>
                        <p class="text-2xl font-bold text-white mt-2">{{ $o['total_queries'] }}</p>
                        <p class="text-xs text-zinc-500 mt-1">Total inquiries received</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 flex-1 lg:items-stretch min-h-0">
                <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 flex flex-col lg:min-h-0">
                    <h3 class="font-bold text-white mb-4 shrink-0">Recent Sales</h3>
                    <div class="flex-1 overflow-y-auto min-h-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-zinc-500 text-xs uppercase">
                                <th class="pb-2">Customer</th><th class="pb-2">Status</th><th class="pb-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @forelse ($o['recent_sales'] as $sale)
                                <tr>
                                    <td class="py-2 text-white">{{ $sale->customer?->company_name }}</td>
                                    <td class="py-2">
                                        <x-badge :color="$sale->payment_status === 'paid' ? 'green' : ($sale->payment_status === 'partial' ? 'orange' : 'gray')">
                                            {{ ucfirst($sale->payment_status) }}
                                        </x-badge>
                                    </td>
                                    <td class="py-2 text-right text-white">MVR {{ number_format($sale->grand_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-4 text-center text-zinc-500">No sales yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 flex flex-col lg:min-h-0">
                    <h3 class="font-bold text-white mb-1 shrink-0">Recent Customers</h3>
                    <p class="text-xs text-zinc-500 mb-4 shrink-0">You added {{ $o['customers_this_month'] }} customers this month.</p>
                    <div class="flex-1 overflow-y-auto min-h-0 space-y-3">
                        @forelse ($o['recent_customers'] as $customer)
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-accent/20 text-accent text-xs font-semibold flex items-center justify-center">
                                    {{ mb_substr($customer->company_name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white truncate">{{ $customer->company_name }}</p>
                                    <p class="text-xs text-zinc-500">{{ $customer->created_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">No customers yet.</p>
                        @endforelse
                    </div>
                </div>
                </div>
            </div>
        </div>
    @endif

    @if ($tab === 'analytics')
        @php($a = $this->analytics)
        <div class="mb-4">
            <h2 class="text-lg font-bold text-white">Analytics Overview</h2>
            <p class="text-sm text-zinc-500">Key metrics for the selected period.</p>
        </div>
        <div class="flex gap-3 mb-4">
            <select wire:model="period" class="rounded-lg bg-zinc-800 border-white/10 text-white text-sm">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
            <input type="date" wire:model="analyticsDate" class="rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
        </div>

        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 mb-6">
            <h3 class="font-bold text-white mb-1">Total Sales Trend</h3>
            <p class="text-xs text-zinc-500 mb-4">Last 6 {{ $period }} periods, ending {{ \Illuminate\Support\Carbon::parse($analyticsDate)->format('d M Y') }}.</p>
            <x-bar-chart :points="$this->salesTrend" />
        </div>

        <div class="rounded-xl bg-zinc-800 border border-white/10 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-zinc-700/50">
                    <tr class="text-left text-zinc-400 text-xs uppercase">
                        <th class="p-3">Metric</th><th class="p-3">Value</th><th class="p-3">Change</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr>
                        <td class="p-3 text-zinc-300">Target</td>
                        <td class="p-3 text-white">{{ $a['target'] ? 'MVR '.number_format($a['target']->sales, 2) : '—' }}</td>
                        <td class="p-3 text-zinc-500">—</td>
                    </tr>
                    @foreach ($a['rows'] as $label => $row)
                        <tr>
                            <td class="p-3 text-zinc-300">{{ $label }}</td>
                            <td class="p-3 text-white">
                                {{ str_contains($label, 'Value') || str_contains($label, 'Sales') ? 'MVR '.number_format($row['value'], 2) : number_format($row['value'], $label === 'Achieved %' ? 1 : 0) }}
                                @if ($label === 'Achieved %') % @endif
                            </td>
                            <td class="p-3 {{ $row['change'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $row['change'] >= 0 ? '▲' : '▼' }} {{ abs($row['change']) }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($tab === 'staff')
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <div class="flex rounded-lg border border-white/10 overflow-hidden text-sm">
                @foreach (['daily' => 'Daily', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $val => $label)
                    <button wire:click="$set('staffTimeframe', '{{ $val }}')"
                        class="px-3 py-2 {{ $staffTimeframe === $val ? 'bg-accent text-white' : 'text-zinc-400' }}">{{ $label }}</button>
                @endforeach
            </div>
            <input type="date" wire:model="staffDate" class="rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
        </div>

        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 mb-6">
            <h3 class="font-bold text-white mb-1">Sales by Staff</h3>
            <p class="text-xs text-zinc-500 mb-4">Ranked for the selected period. Click a bar to see that staff member's details below.</p>
            @php($maxSales = $this->staffList->max('period_sales') ?: 1)
            <div class="space-y-3">
                @forelse ($this->staffList as $member)
                    @php($isSelected = ($selectedStaffId ?? $this->staffList->first()?->id) === $member->id)
                    @php($pct = $maxSales > 0 ? max(($member->period_sales / $maxSales) * 100, 2) : 0)
                    <button type="button" wire:click="$set('selectedStaffId', {{ $member->id }})" class="w-full flex items-center gap-3 group">
                        <span class="w-28 shrink-0 text-left text-xs {{ $isSelected ? 'text-white font-semibold' : 'text-zinc-400' }} truncate">{{ $member->name }}</span>
                        <span class="flex-1 h-5 rounded-full bg-zinc-900 overflow-hidden relative">
                            <span class="absolute inset-y-0 left-0 rounded-full transition-all {{ $isSelected ? 'bg-accent' : 'bg-zinc-600 group-hover:bg-zinc-500' }}" style="width: {{ $pct }}%"></span>
                        </span>
                        <span class="w-28 shrink-0 text-right text-xs {{ $isSelected ? 'text-white font-semibold' : 'text-zinc-400' }}">MVR {{ number_format($member->period_sales, 0) }}</span>
                    </button>
                @empty
                    <p class="text-sm text-zinc-500">No staff found.</p>
                @endforelse
            </div>
        </div>

        @if ($this->selectedStaff)
            @php($s = $this->selectedStaff)
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-12 w-12 rounded-full bg-accent/20 text-accent flex items-center justify-center font-bold text-lg">
                        {{ mb_substr($s->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-bold text-white">{{ $s->name }}</p>
                        <p class="text-xs text-zinc-500">{{ ucfirst($s->role) }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-sm">
                    <div><p class="text-zinc-500 text-xs">Total Sales</p><p class="text-white font-semibold">MVR {{ number_format($s->kpi_sales, 2) }}</p></div>
                    <div><p class="text-zinc-500 text-xs">Leads Entered</p><p class="text-white font-semibold">{{ $s->kpi_leads }}</p></div>
                    <div><p class="text-zinc-500 text-xs">Quotations Created</p><p class="text-white font-semibold">{{ $s->kpi_quotations }}</p></div>
                    <div><p class="text-zinc-500 text-xs">Calls Made</p><p class="text-white font-semibold">{{ $s->kpi_calls }}</p></div>
                    <div><p class="text-zinc-500 text-xs">Potential Deals</p><p class="text-white font-semibold">{{ $s->kpi_potential_deals }}</p></div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($this->staffList as $member)
                <button wire:click="$set('selectedStaffId', {{ $member->id }})"
                    class="text-left rounded-xl border p-4 {{ ($selectedStaffId ?? $this->staffList->first()?->id) === $member->id ? 'border-accent bg-accent/10' : 'border-white/10 bg-zinc-800' }}">
                    <div class="h-9 w-9 rounded-full bg-accent/20 text-accent flex items-center justify-center font-semibold mb-2">
                        {{ mb_substr($member->name, 0, 1) }}
                    </div>
                    <p class="text-sm font-semibold text-white truncate">{{ $member->name }}</p>
                    <p class="text-xs text-zinc-500">{{ ucfirst($member->role) }}</p>
                    <p class="text-xs text-accent mt-1">MVR {{ number_format($member->period_sales, 2) }}</p>
                </button>
            @endforeach
        </div>
    @endif
</div>
