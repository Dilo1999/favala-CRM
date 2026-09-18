@if ($variant === 'popup')
    {{--
        Site-wide floating chat widget — the icon-only version of the Fava bot
        shown big on the Dashboard's Overview tab. Lives in the main layout so
        it's on every CRM page. Hidden specifically on the Dashboard's Overview
        tab, since the big panel already covers that spot there (including on
        live tab switches, which Livewire does without a page reload).
    --}}
    <div x-data="{ open: false, hiddenForOverview: {{ request()->routeIs('crm.dashboard') ? 'true' : 'false' }} }"
        x-on:dashboard-tab-changed.window="hiddenForOverview = ($event.detail.tab === 'overview'); if (hiddenForOverview) open = false"
        x-show="!hiddenForOverview" x-cloak
        class="fixed bottom-6 right-6 z-40">
        <div x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click.outside="open = false" @keydown.escape.window="open = false"
            class="absolute bottom-[4.5rem] right-0 w-80 max-w-[calc(100vw-3rem)] h-[28rem] max-h-[calc(100vh-8rem)] rounded-2xl bg-zinc-800 border border-white/10 shadow-2xl shadow-black/50 overflow-hidden flex flex-col">
            <div class="flex items-center gap-3 p-4 border-b border-white/10 bg-zinc-900/60 shrink-0">
                <img src="{{ asset('images/bot/head_assembly.svg') }}" alt="Fava" class="w-9 h-9 object-contain shrink-0" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-white">Fava</p>
                    <p class="text-xs text-zinc-500">Your Favala assistant</p>
                </div>
                <button type="button" @click="open = false" class="text-zinc-500 hover:text-white p-1 -m-1">
                    <x-heroicon-o-x class="w-4 h-4" />
                </button>
            </div>

            @include('livewire.fava-chat._body')
        </div>

        <button type="button" @click="open = !open" title="Chat with Fava"
            :class="{ 'zaha-launcher-btn': !open }"
            class="h-14 w-14 rounded-full bg-accent hover:bg-accent-hover shadow-lg shadow-black/40 flex items-center justify-center transition-transform hover:scale-105">
            <img src="{{ asset('images/bot/head_assembly.svg') }}" alt="Fava" class="w-9 h-9 object-contain" />
            <span x-show="!open" x-cloak class="zaha-launcher-dot" aria-hidden="true"></span>
        </button>
    </div>
@else
    {{-- Big Fava panel on the Dashboard's Overview tab. --}}
    {{-- Fixed height (not min-height): the panel must stop growing with the
         conversation so the messages list's own overflow-y-auto is what
         scrolls, instead of the whole card (and page) stretching taller. --}}
    <div class="lg:w-1/3 shrink-0 rounded-xl bg-zinc-800 border border-white/10 flex flex-col lg:h-[calc(100vh-12rem)]"
        x-data="{ mood: 'idle', getCurrentSvg() { return 'bot-' + this.mood + '.svg'; }, getMoodCategory() { return this.mood; } }">
        <div class="shrink-0 flex flex-col items-center justify-start text-center p-5 pt-6">
            <div class="w-36 -translate-x-2 flex flex-col items-center">
                <div class="relative zaha-bot-hover mb-2 w-36">
                    <div class="absolute inset-0 rounded-full bg-accent/20 blur-2xl"></div>
                    <span class="zaha-bot-sparkle zaha-bot-sparkle--1" aria-hidden="true">✨</span>
                    <span class="zaha-bot-sparkle zaha-bot-sparkle--2" aria-hidden="true">⭐</span>
                    <span class="zaha-bot-sparkle zaha-bot-sparkle--3" aria-hidden="true">✨</span>
                    <span class="zaha-bot-sparkle zaha-bot-sparkle--4" aria-hidden="true">⭐</span>
                    {{-- <object>, not <img>: an <img>-embedded SVG can't receive mouse
                         hover internally, so the head/hand parts inside bot-idle.svg
                         (its own :hover rules) would never fire. --}}
                    <object type="image/svg+xml" :data="'{{ asset('images/bot') }}/' + getCurrentSvg()" :class="'animate-' + getMoodCategory()"
                        aria-label="Fava" class="zaha-dynamic-bot w-36 h-36 object-contain animate-idle" data="{{ asset('images/bot/bot-idle.svg') }}"></object>
                    <div class="zaha-speech-bubble" aria-hidden="true">
                        <span class="zaha-speech-bubble__spark" aria-hidden="true">✨</span>
                        <span class="zaha-speech-bubble__text">I love to help you!</span>
                    </div>
                </div>
                <h3 class="text-white font-bold text-lg">Hey, {{ explode(' ', auth()->user()->name)[0] }}</h3>
                <p class="text-sm text-zinc-500 mt-1">How can I help you today?</p>
            </div>
        </div>

        @include('livewire.fava-chat._body')
    </div>
@endif
