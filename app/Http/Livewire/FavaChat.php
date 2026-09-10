<?php

namespace App\Http\Livewire;

use App\Models\AiChatMessage;
use App\Services\FavaAssistantService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * The Fava chat widget, rendered in two chrome variants (see
 * resources/views/livewire/fava-chat.blade.php) that share one persisted
 * conversation per user: 'popup' — the site-wide floating launcher — and
 * 'panel' — the big panel on the Dashboard's Overview tab.
 */
class FavaChat extends Component
{
    public string $variant = 'popup';

    public string $draft = '';

    protected $listeners = ['favaMessageSent' => '$refresh'];

    public function mount(string $variant = 'popup'): void
    {
        $this->variant = $variant;
    }

    /**
     * Phase 1 of sending — fast: just persists the user's message and clears
     * the input. Kept separate from generateReply() (the slow AI call) so the
     * browser shows the message immediately instead of appearing to do
     * nothing for however long the AI call takes. The two are chained back
     * to back client-side (see resources/views/livewire/fava-chat/_body.blade.php).
     */
    public function sendMessage(): void
    {
        $this->validate(['draft' => 'required|string|max:2000']);

        $key = 'fava-chat:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            session()->flash('fava-error', "You're sending messages too quickly — please wait a moment.");

            return;
        }
        RateLimiter::hit($key, 60);

        AiChatMessage::create([
            'user_id' => auth()->id(),
            'role' => AiChatMessage::ROLE_USER,
            'content' => $this->draft,
        ]);

        $this->draft = '';

        $this->emit('favaMessageSent');
    }

    /** Phase 2 — the slow part: get and persist Fava's reply to the latest pending message. */
    public function generateReply(FavaAssistantService $assistant): void
    {
        set_time_limit(60);

        $latest = AiChatMessage::where('user_id', auth()->id())->latest('id')->first();

        // Nothing pending, or it's already been answered (e.g. this got
        // triggered twice for the same turn) — nothing to do.
        if (! $latest || $latest->role !== AiChatMessage::ROLE_USER) {
            return;
        }

        $assistant->replyTo(auth()->user(), $latest);

        $this->emit('favaMessageSent');
    }

    public function getMessagesProperty()
    {
        return AiChatMessage::where('user_id', auth()->id())
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();
    }

    public function render()
    {
        return view('livewire.fava-chat');
    }
}
