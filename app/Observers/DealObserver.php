<?php

namespace App\Observers;

use App\Models\Deal;

class DealObserver
{
    public function creating(Deal $deal): void
    {
        if (! $deal->expires_at) {
            $deal->expires_at = now()->addDays(config('crm.document_expiry_days'));
        }
    }
}
