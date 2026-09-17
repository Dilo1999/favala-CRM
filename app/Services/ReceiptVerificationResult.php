<?php

namespace App\Services;

/** Outcome of comparing a receipt's printed reference number against the one typed into the Receive Payment form. */
final class ReceiptVerificationResult
{
    private function __construct(
        public readonly string $status,
        public readonly ?string $extractedReference,
    ) {}

    public static function match(string $extractedReference): self
    {
        return new self('match', $extractedReference);
    }

    public static function mismatch(string $extractedReference): self
    {
        return new self('mismatch', $extractedReference);
    }

    /** Claude couldn't read a reference number off the receipt, or the API call itself failed — never treated as a mismatch. */
    public static function inconclusive(): self
    {
        return new self('inconclusive', null);
    }

    public function isMismatch(): bool
    {
        return $this->status === 'mismatch';
    }
}
