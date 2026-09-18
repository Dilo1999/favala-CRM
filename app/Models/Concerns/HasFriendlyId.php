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

    /**
     * Reverse of getFriendlyIdAttribute(): turns whatever a user typed into a
     * search box — "INV-0007", "inv7", or just "0007" — back into the raw id,
     * so search can match what the UI actually displays. Returns null when the
     * value doesn't look like this model's friendly id (e.g. a customer name).
     */
    public static function idFromFriendlyId(string $value): ?int
    {
        $value = trim($value);
        $prefix = preg_quote(static::friendlyIdPrefix(), '/');

        if (preg_match("/^{$prefix}-?\s*0*(\d+)$/i", $value, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/^0*(\d+)$/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
