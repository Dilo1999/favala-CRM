<div>
    <h1 class="text-2xl font-bold text-white mb-6">Targets</h1>

    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="flex rounded-lg border border-white/10 overflow-hidden text-sm">
            @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $val => $label)
                <button wire:click="$set('period', '{{ $val }}')" class="px-3 py-2 font-medium {{ $period === $val ? 'bg-accent text-white' : 'text-zinc-400 hover:bg-zinc-700' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="relative" x-data="datePicker('anchorDate', '{{ $anchorDate }}')" wire:ignore>
            <x-heroicon-o-calendar class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input type="text" x-ref="input" readonly class="pl-9 pr-3 rounded-lg border border-white/10 bg-zinc-800 text-white text-sm font-medium cursor-pointer" />
        </div>
    </div>

    <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 mb-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="font-bold text-white">Company {{ ucfirst($period) }} Targets for {{ $this->periodLabel }}</h2>
                <p class="text-sm text-zinc-500 mt-0.5">Overall goals for the entire team for the selected period.</p>
            </div>
            <button wire:click="openCompanyForm" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold whitespace-nowrap">
                <x-heroicon-o-pencil class="w-4 h-4" /> Set Company Targets
            </button>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach ([
                ['key' => 'sales', 'label' => 'Sales', 'icon' => 'currency-dollar', 'money' => true],
                ['key' => 'quotations', 'label' => 'Quotations', 'icon' => 'document-text'],
                ['key' => 'deals', 'label' => 'Deals', 'icon' => 'clipboard-list'],
                ['key' => 'meetings', 'label' => 'Meetings', 'icon' => 'users'],
                ['key' => 'calls', 'label' => 'Calls', 'icon' => 'phone'],
                ['key' => 'site_visits', 'label' => 'Site Visits', 'icon' => 'office-building'],
                ['key' => 'new_leads', 'label' => 'New Leads', 'icon' => 'user-add'],
            ] as $kpi)
                @php($target = $this->companyTarget?->{$kpi['key']} ?? 0)
                @php($achieved = $this->companyAchieved[$kpi['key']] ?? 0)
                @php($remaining = $this->remaining($achieved, $target))
                <div class="rounded-lg bg-zinc-900 border border-white/10 p-4">
                    <div class="flex items-start justify-between">
                        <p class="text-sm text-zinc-400">{{ $kpi['label'] }}</p>
                        @switch($kpi['icon'])
                            @case('currency-dollar') <x-heroicon-o-currency-dollar class="w-5 h-5 text-accent" /> @break
                            @case('document-text') <x-heroicon-o-document-text class="w-5 h-5 text-accent" /> @break
                            @case('clipboard-list') <x-heroicon-o-clipboard-list class="w-5 h-5 text-accent" /> @break
                            @case('users') <x-heroicon-o-users class="w-5 h-5 text-accent" /> @break
                            @case('phone') <x-heroicon-o-phone class="w-5 h-5 text-accent" /> @break
                            @case('office-building') <x-heroicon-o-office-building class="w-5 h-5 text-accent" /> @break
                            @case('user-add') <x-heroicon-o-user-add class="w-5 h-5 text-accent" /> @break
                        @endswitch
                    </div>
                    <p class="text-2xl font-bold text-white mt-2">
                        @if (!empty($kpi['money']))
                            <span class="text-base font-semibold text-zinc-400 align-top">MVR</span> {{ $this->formatCompact($remaining) }}
                        @else
                            {{ number_format($remaining) }}
                        @endif
                    </p>
                    <p class="text-xs text-zinc-500 mt-1">remaining ·
                        @if (!empty($kpi['money']))
                            MVR {{ $this->formatCompact($achieved) }} / {{ $this->formatCompact($target) }} achieved
                        @else
                            {{ number_format($achieved) }} / {{ number_format($target) }} achieved
                        @endif
                    </p>
                    <div class="h-1 bg-zinc-700 rounded-full mt-2 overflow-hidden"><div class="h-full bg-accent" style="width: {{ $this->progressPercent($achieved, $target) }}%"></div></div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="font-bold text-white">Staff {{ ucfirst($period) }} Targets</h2>
                <p class="text-sm text-zinc-500 mt-0.5">Breakdown of sales and activity targets for your team.</p>
            </div>
            <button wire:click="openStaffForm" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold whitespace-nowrap">
                <x-heroicon-o-plus-circle class="w-4 h-4" /> Set Staff Targets
            </button>
        </div>
        <div class="overflow-x-auto -mx-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                        <th class="px-5 py-3">Staff</th>
                        <th class="px-5 py-3">Sales Target</th>
                        <th class="px-5 py-3">Calls Target</th>
                        <th class="px-5 py-3">Meetings Target</th>
                        <th class="px-5 py-3">New Leads Target</th>
                        <th class="px-5 py-3">Site Visits Target</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach ($this->staffRows as $user)
                        <tr class="hover:bg-zinc-700/30">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    @if ($user->avatar)
                                        <img src="{{ \Illuminate\Support\Str::startsWith($user->avatar, ['http://', 'https://']) ? $user->avatar : \Illuminate\Support\Facades\Storage::url($user->avatar) }}" class="h-8 w-8 rounded-full object-cover shrink-0" alt="{{ $user->name }}" />
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-accent/20 text-accent flex items-center justify-center text-xs font-semibold shrink-0">{{ mb_substr($user->name, 0, 1) }}</div>
                                    @endif
                                    <span class="text-white font-medium">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-zinc-300 min-w-[110px]">
                                <span>MVR {{ number_format($user->achieved['sales'], 0) }} / {{ number_format($user->target?->sales ?? 0, 0) }}</span>
                                <div class="h-1 bg-zinc-700 rounded-full mt-1.5 overflow-hidden"><div class="h-full bg-accent" style="width: {{ $this->progressPercent($user->achieved['sales'], $user->target?->sales ?? 0) }}%"></div></div>
                            </td>
                            <td class="px-5 py-3 text-zinc-300 min-w-[90px]">
                                <span>{{ $user->achieved['calls'] }} / {{ $user->target?->calls ?? 0 }}</span>
                                <div class="h-1 bg-zinc-700 rounded-full mt-1.5 overflow-hidden"><div class="h-full bg-accent" style="width: {{ $this->progressPercent($user->achieved['calls'], $user->target?->calls ?? 0) }}%"></div></div>
                            </td>
                            <td class="px-5 py-3 text-zinc-300 min-w-[90px]">
                                <span>{{ $user->achieved['meetings'] }} / {{ $user->target?->meetings ?? 0 }}</span>
                                <div class="h-1 bg-zinc-700 rounded-full mt-1.5 overflow-hidden"><div class="h-full bg-accent" style="width: {{ $this->progressPercent($user->achieved['meetings'], $user->target?->meetings ?? 0) }}%"></div></div>
                            </td>
                            <td class="px-5 py-3 text-zinc-300 min-w-[90px]">
                                <span>{{ $user->achieved['new_leads'] }} / {{ $user->target?->new_leads ?? 0 }}</span>
                                <div class="h-1 bg-zinc-700 rounded-full mt-1.5 overflow-hidden"><div class="h-full bg-accent" style="width: {{ $this->progressPercent($user->achieved['new_leads'], $user->target?->new_leads ?? 0) }}%"></div></div>
                            </td>
                            <td class="px-5 py-3 text-zinc-300 min-w-[90px]">
                                <span>{{ $user->achieved['site_visits'] }} / {{ $user->target?->site_visits ?? 0 }}</span>
                                <div class="h-1 bg-zinc-700 rounded-full mt-1.5 overflow-hidden"><div class="h-full bg-accent" style="width: {{ $this->progressPercent($user->achieved['site_visits'], $user->target?->site_visits ?? 0) }}%"></div></div>
                            </td>
                            <td class="px-5 py-3 text-right"><button wire:click="openStaffForm({{ $user->id }})" class="text-accent text-xs font-medium hover:underline">Set Target</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($showCompanyForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showCompanyForm', false)">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">Set Company Targets — {{ ucfirst($period) }}</h2>
                <form wire:submit.prevent="saveCompanyTarget" class="grid grid-cols-2 gap-4">
                    @foreach (['sales' => 'Sales (MVR)', 'quotations' => 'Quotations', 'deals' => 'Deals', 'meetings' => 'Meetings', 'calls' => 'Calls', 'site_visits' => 'Site Visits', 'new_leads' => 'New Leads'] as $key => $label)
                        <div>
                            <label class="block text-xs text-zinc-400 mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" wire:model="companyForm.{{ $key }}" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                        </div>
                    @endforeach
                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showCompanyForm', false)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showStaffForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showStaffForm', false)">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">Set Staff Targets — {{ ucfirst($period) }}</h2>
                <form wire:submit.prevent="saveStaffTarget" class="space-y-4">
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Staff Member</label>
                        <select wire:model="staffFormUserId" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                            @foreach ($staffOptions as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach (['sales' => 'Sales (MVR)', 'calls' => 'Calls', 'meetings' => 'Meetings', 'new_leads' => 'New Leads', 'site_visits' => 'Site Visits'] as $key => $label)
                            <div>
                                <label class="block text-xs text-zinc-400 mb-1">{{ $label }}</label>
                                <input type="number" step="0.01" wire:model="staffForm.{{ $key }}" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showStaffForm', false)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
