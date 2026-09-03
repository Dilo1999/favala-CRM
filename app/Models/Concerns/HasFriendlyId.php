<?php

namespace App\Models\Concerns;

/**
 * Adds a stable, human-friendly display id such as DEAL-0001, QT-0042.
 * Never exposes the raw database id to the UI — every list/column/reference
 * that shows this record must use `friendly_id`, not `id`.
 */
trait HasFriendlyId
{
    public function initializeHasFriendlyId(): void
    {
        $this->append('friendly_id');
    }

    public function getFriendlyIdAttribute(): ?string
    {
        if (! $this->exists) {
            return null;
        }

        return static::friendlyIdPrefix().'-'.str_pad((string) $this->getKey(), 4, '0', STR_PAD_LEFT);
    }

    abstract public static function friendlyIdPrefix(): string;
}
