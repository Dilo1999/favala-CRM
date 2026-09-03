<div>
    <h1 class="text-2xl font-bold text-white mb-6">Targets</h1>

    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="flex rounded-lg border border-gray-800 overflow-hidden text-sm">
            @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $val => $label)
                <button wire:click="$set('period', '{{ $val }}')" class="px-3 py-2 {{ $period === $val ? 'bg-orange-600 text-white' : 'text-gray-400' }}">{{ $label }}</button>
            @endforeach
        </div>
        <input type="date" wire:model="anchorDate" class="rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
    </div>

    <div class="rounded-xl bg-gray-900 border border-gray-800 p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-white">Company {{ ucfirst($period) }} Targets for {{ $this->periodLabel }}</h2>
            <button wire:click="openCompanyForm" class="px-3 py-1.5 rounded-lg border border-gray-700 text-gray-300 text-sm">Set Company Targets</button>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4">
            @foreach ([
                ['key' => 'sales', 'label' => 'Sales', 'money' => true],
                ['key' => 'quotations', 'label' => 'Quotations'],
                ['key' => 'deals', 'label' => 'Deals'],
                ['key' => 'meetings', 'label' => 'Meetings'],
                ['key' => 'calls', 'label' => 'Calls'],
                ['key' => 'site_visits', 'label' => 'Site Visits'],
                ['key' => 'new_leads', 'label' => 'New Leads'],
            ] as $kpi)
                <div class="rounded-lg bg-gray-950 border border-gray-800 p-3">
                    <p class="text-xs text-gray-500">{{ $kpi['label'] }}</p>
                    <p class="text-sm font-bold text-white mt-1">
                        {{ !empty($kpi['money']) ? number_format($this->companyAchieved[$kpi['key']], 0) : $this->companyAchieved[$kpi['key']] }}
                        <span class="text-gray-500 font-normal">/ {{ $this->companyTarget?->{$kpi['key']} ?? 0 }}</span>
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
        <h2 class="font-bold text-white mb-4">Staff Targets</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 text-xs uppercase border-b border-gray-800">
                        <th class="p-2">Staff</th><th class="p-2">Sales</th><th class="p-2">Calls</th><th class="p-2">Meetings</th><th class="p-2">New Leads</th><th class="p-2">Site Visits</th><th class="p-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach ($this->staffRows as $user)
                        <tr>
                            <td class="p-2 text-white">{{ $user->name }}</td>
                            <td class="p-2 text-gray-300">{{ number_format($user->achieved['sales'], 0) }} / {{ $user->target?->sales ?? 0 }}</td>
                            <td class="p-2 text-gray-300">{{ $user->achieved['calls'] }} / {{ $user->target?->calls ?? 0 }}</td>
                            <td class="p-2 text-gray-300">{{ $user->achieved['meetings'] }} / {{ $user->target?->meetings ?? 0 }}</td>
                            <td class="p-2 text-gray-300">{{ $user->achieved['new_leads'] }} / {{ $user->target?->new_leads ?? 0 }}</td>
                            <td class="p-2 text-gray-300">{{ $user->achieved['site_visits'] }} / {{ $user->target?->site_visits ?? 0 }}</td>
                            <td class="p-2 text-right"><button wire:click="openStaffForm({{ $user->id }})" class="text-orange-400 text-xs">Set Target</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($showCompanyForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showCompanyForm', false)">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">Set Company Targets — {{ ucfirst($period) }}</h2>
                <form wire:submit.prevent="saveCompanyTarget" class="grid grid-cols-2 gap-4">
                    @foreach (['sales' => 'Sales (MVR)', 'quotations' => 'Quotations', 'deals' => 'Deals', 'meetings' => 'Meetings', 'calls' => 'Calls', 'site_visits' => 'Site Visits', 'new_leads' => 'New Leads'] as $key => $label)
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" wire:model="companyForm.{{ $key }}" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                        </div>
                    @endforeach
                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showCompanyForm', false)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showStaffForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showStaffForm', false)">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">Set Staff Targets — {{ ucfirst($period) }}</h2>
                <form wire:submit.prevent="saveStaffTarget" class="grid grid-cols-2 gap-4">
                    @foreach (['sales' => 'Sales (MVR)', 'calls' => 'Calls', 'meetings' => 'Meetings', 'new_leads' => 'New Leads', 'site_visits' => 'Site Visits'] as $key => $label)
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" wire:model="staffForm.{{ $key }}" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                        </div>
                    @endforeach
                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showStaffForm', false)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
