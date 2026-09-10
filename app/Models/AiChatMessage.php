<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiChatMessage extends Model
{
    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = ['user_id', 'role', 'content', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Fava's replies come back as Markdown (tables, bold, lists — see the
     * system prompt in FavaAssistantService), so render it to HTML for
     * display instead of dumping the raw "| a | b |" syntax in the bubble.
     * html_input=escape (never "allow") keeps this safe even though the
     * text is model-generated: tool results a prompt-injection attempt
     * could steer the model to echo are untrusted, so any literal HTML in
     * the content is shown as text rather than executed.
     */
    public function getRenderedHtmlAttribute(): string
    {
        return Str::markdown($this->content, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }
}
