{{--
    Site-wide floating chat widget — the icon-only version of the Fava bot shown
    big on the Dashboard's Overview tab. Lives in the main layout so it's on
    every CRM page. Clicking it never opens the full dashboard panel, only this
    small popup. Hidden specifically on the Dashboard's Overview tab, since the
    big panel already covers that spot there (including on live tab switches,
    which Livewire does without a page reload).
--}}
<div x-data="{ open: false, hiddenForOverview: {{ request()->routeIs('crm.dashboard') ? 'true' : 'false' }} }"
    x-on:dashboard-tab-changed.window="hiddenForOverview = ($event.detail.tab === 'overview'); if (hiddenForOverview) open = false"
    x-show="!hiddenForOverview" x-cloak
    class="fixed bottom-6 right-6 z-40">
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click.outside="open = false" @keydown.escape.window="open = false"
        class="absolute bottom-[4.5rem] right-0 w-80 max-w-[calc(100vw-3rem)] rounded-2xl bg-zinc-800 border border-white/10 shadow-2xl shadow-black/50 overflow-hidden flex flex-col">
        <div class="flex items-center gap-3 p-4 border-b border-white/10 bg-zinc-900/60 shrink-0">
            <img src="{{ asset('images/zaha/bot/head_assembly.svg') }}" alt="Fava" class="w-9 h-9 object-contain shrink-0" />
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-white">Fava</p>
                <p class="text-xs text-zinc-500">Your Favala assistant</p>
            </div>
            <button type="button" @click="open = false" class="text-zinc-500 hover:text-white p-1 -m-1">
                <x-heroicon-o-x class="w-4 h-4" />
            </button>
        </div>

        <div class="p-4 h-56 overflow-y-auto text-sm space-y-3">
            <div class="flex items-start gap-2">
                <img src="{{ asset('images/zaha/bot/head_assembly.svg') }}" alt="" class="w-6 h-6 shrink-0 mt-0.5 object-contain" />
                <div class="bg-zinc-700/60 rounded-lg rounded-tl-none px-3 py-2 text-zinc-200 max-w-[85%]">
                    Hi, I'm Fava! How can I help you today?
                </div>
            </div>
        </div>

        <form class="flex items-center gap-2 p-3 border-t border-white/10 shrink-0">
            <input type="text" placeholder="Message Fava…" class="flex-1 rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            <button type="submit" class="h-9 w-9 shrink-0 flex items-center justify-center rounded-lg bg-accent hover:bg-accent-hover text-white">
                <x-heroicon-o-paper-airplane class="w-4 h-4" />
            </button>
        </form>
    </div>

    <button type="button" @click="open = !open" title="Chat with Fava"
        :class="{ 'zaha-launcher-btn': !open }"
        class="h-14 w-14 rounded-full bg-accent hover:bg-accent-hover shadow-lg shadow-black/40 flex items-center justify-center transition-transform hover:scale-105">
        <img src="{{ asset('images/zaha/bot/head_assembly.svg') }}" alt="Fava" class="w-9 h-9 object-contain" />
        <span x-show="!open" x-cloak class="zaha-launcher-dot" aria-hidden="true"></span>
    </button>
</div>
