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

    public function sendMessage(FavaAssistantService $assistant): void
    {
        set_time_limit(60);

        $this->validate(['draft' => 'required|string|max:2000']);

        $key = 'fava-chat:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            session()->flash('fava-error', "You're sending messages too quickly — please wait a moment.");

            return;
        }
        RateLimiter::hit($key, 60);

        $message = $this->draft;
        $this->draft = '';

        $assistant->reply(auth()->user(), $message);

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
