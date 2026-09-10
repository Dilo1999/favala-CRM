{{--
    Shared message list + input, used by both the popup and panel chrome in
    ../fava-chat.blade.php. Both read/write the same ai_chat_messages rows for
    the current user, and are told to refresh via the favaMessageSent Livewire
    event so they never show stale history while both are mounted on one page
    (e.g. the Dashboard's Overview tab).
--}}
@if (session('fava-error'))
    <div class="px-4 pt-2 text-xs text-red-400">{{ session('fava-error') }}</div>
@endif

<div class="flex-1 overflow-y-auto px-4 py-3 text-sm space-y-3 min-h-0" data-fava-scroll>
    @forelse ($this->messages as $m)
        @if ($m->role === 'user')
            <div class="flex justify-end">
                <div class="bg-accent rounded-lg rounded-tr-none px-3 py-2 text-white max-w-[85%] whitespace-pre-wrap break-words">{{ $m->content }}</div>
            </div>
        @else
            <div class="flex items-start gap-2">
                <img src="{{ asset('images/zaha/bot/head_assembly.svg') }}" alt="" class="w-6 h-6 shrink-0 mt-0.5 object-contain" />
                <div class="fava-markdown bg-zinc-700/60 rounded-lg rounded-tl-none px-3 py-2 text-zinc-200 max-w-[85%] break-words">{!! $m->rendered_html !!}</div>
            </div>
        @endif
    @empty
        <div class="flex items-start gap-2">
            <img src="{{ asset('images/zaha/bot/head_assembly.svg') }}" alt="" class="w-6 h-6 shrink-0 mt-0.5 object-contain" />
            <div class="bg-zinc-700/60 rounded-lg rounded-tl-none px-3 py-2 text-zinc-200 max-w-[85%]">
                Hi, I'm Fava! How can I help you today?
            </div>
        </div>
    @endforelse

    <div wire:loading wire:target="sendMessage,generateReply" class="flex items-start gap-2">
        <img src="{{ asset('images/zaha/bot/head_assembly.svg') }}" alt="" class="w-6 h-6 shrink-0 mt-0.5 object-contain" />
        <div class="bg-zinc-700/60 rounded-lg rounded-tl-none px-3 py-2 text-zinc-400 text-xs italic">
            Fava is thinking…
        </div>
    </div>
</div>

{{--
    Two Livewire round trips chained client-side, not one wire:submit call:
    sendMessage() persists the message and clears the input (fast — the
    message bubble appears right away), then generateReply() runs the slow
    AI call and appends the answer. Doing this in a single method would
    leave the browser showing nothing new — no bubble, no cleared input —
    for the AI call's whole duration, which reads as the send doing nothing.
--}}
<form @submit.prevent="await $wire.sendMessage(); await $wire.generateReply()" class="flex items-center gap-2 p-3 border-t border-white/10 shrink-0">
    <input type="text" wire:model.defer="draft" placeholder="Message Fava…" autocomplete="off"
        class="flex-1 rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
    <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage,generateReply"
        class="h-9 w-9 shrink-0 flex items-center justify-center rounded-lg bg-accent hover:bg-accent-hover text-white disabled:opacity-50">
        <x-heroicon-o-paper-airplane class="w-4 h-4" />
    </button>
</form>
@error('draft')
    <p class="px-3 pb-2 text-xs text-red-400">{{ $message }}</p>
@enderror
