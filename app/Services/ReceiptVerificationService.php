<?php

namespace App\Services;

use Anthropic\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Reads an uploaded payment receipt (image or PDF) with Claude's vision and
 * checks whether the reference/transaction number printed on it matches what
 * the user typed into the Receive Payment form. Advisory only: any failure to
 * read the receipt (blurry photo, API error, no number visible) comes back
 * inconclusive rather than as a mismatch, so a bad read can never block a
 * legitimate payment on its own — the caller decides whether/how to warn.
 */
class ReceiptVerificationService
{
    public function __construct(private Client $client) {}

    public function verify(UploadedFile $receipt, string $expectedReference): ReceiptVerificationResult
    {
        try {
            $extracted = $this->extractReference($receipt);
        } catch (\Throwable $e) {
            Log::warning('Receipt verification failed', ['error' => $e->getMessage()]);

            return ReceiptVerificationResult::inconclusive();
        }

        if ($extracted === null) {
            return ReceiptVerificationResult::inconclusive();
        }

        return $this->normalize($extracted) === $this->normalize($expectedReference)
            ? ReceiptVerificationResult::match($extracted)
            : ReceiptVerificationResult::mismatch($extracted);
    }

    private function extractReference(UploadedFile $receipt): ?string
    {
        $mediaType = $receipt->getMimeType() ?: $receipt->getClientMimeType();
        $data = base64_encode(file_get_contents($receipt->getRealPath()));

        $contentBlock = $mediaType === 'application/pdf'
            ? ['type' => 'document', 'source' => ['type' => 'base64', 'media_type' => $mediaType, 'data' => $data]]
            : ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $mediaType, 'data' => $data]];

        $message = $this->client->messages->create(
            maxTokens: 256,
            model: config('ai.anthropic.model'),
            temperature: 0,
            system: <<<'PROMPT'
                You check payment receipts for a CRM. Look at the attached receipt and find the payment reference / transaction / cheque number printed on it — the identifier a bank or payment provider would use to look this specific transaction up. Ignore invoice numbers, account numbers, and amounts.

                Reply with ONLY a JSON object and nothing else: {"reference_found": "<the number exactly as printed>"} or {"reference_found": null} if no such number is visible.
                PROMPT,
            messages: [[
                'role' => 'user',
                'content' => [
                    $contentBlock,
                    ['type' => 'text', 'text' => 'What reference/transaction number is on this receipt?'],
                ],
            ]],
        );

        $text = collect($message->content)
            ->filter(fn ($block) => $block->type === 'text')
            ->map(fn ($block) => $block->text)
            ->implode('');

        if (! preg_match('/\{.*\}/s', $text, $matches)) {
            return null;
        }

        $value = json_decode($matches[0], true)['reference_found'] ?? null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function normalize(string $value): string
    {
        return strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $value));
    }
}
