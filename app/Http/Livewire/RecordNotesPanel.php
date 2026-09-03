<?php

namespace App\Http\Livewire;

use App\Models\RecordNote;
use Livewire\Component;

/**
 * Lightweight per-record chat/update timeline, embedded on Deal, Delivery and
 * Query detail views (spec §4 — "chat/update threads").
 */
class RecordNotesPanel extends Component
{
    public string $notableType;

    public int $notableId;

    public string $message = '';

    public function mount(string $notableType, int $notableId): void
    {
        $this->notableType = $notableType;
        $this->notableId = $notableId;
    }

    public function addNote(): void
    {
        $this->validate(['message' => 'required|string|max:2000']);

        RecordNote::create([
            'notable_type' => $this->notableType,
            'notable_id' => $this->notableId,
            'user_id' => auth()->id(),
            'message' => $this->message,
        ]);

        $this->message = '';
    }

    public function getNotesProperty()
    {
        return RecordNote::with('user')
            ->where('notable_type', $this->notableType)
            ->where('notable_id', $this->notableId)
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.record-notes-panel');
    }
}
