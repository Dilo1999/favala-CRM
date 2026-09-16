<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Credit Note {{ $record->friendly_id }} · Favala</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: #fff !important; }
            .print-frame { border-width: 3px !important; box-shadow: none !important; }
        }
        body { background: #f4f4f5; color: #111; }
    </style>
</head>
<body class="p-10">
    <div class="no-print max-w-3xl mx-auto text-right mb-4">
        <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-accent text-white text-sm font-semibold">Print</button>
    </div>

    <div class="print-frame max-w-3xl mx-auto bg-white text-sm border-[3px] border-black rounded-xl p-8">
        @include('crm.print._header', ['title' => 'CREDIT NOTE'])

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <p class="font-bold text-gray-900 mb-1">Credited To:</p>
                <p class="text-gray-900 font-medium">{{ $record->customer?->company_name }}</p>
                @if ($record->customer?->phone)
                    <p class="text-gray-600">{{ $record->customer->phone }}</p>
                @endif
            </div>
            <div class="text-right space-y-0.5">
                <p><span class="font-bold text-gray-900">Credit Note #:</span> CN-{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}</p>
                <p><span class="font-bold text-gray-900">Return #:</span> {{ $record->friendly_id }}</p>
                <p><span class="font-bold text-gray-900">Date:</span> {{ optional($record->refund_applied_at)->format('F jS, Y') ?? $record->date->format('F jS, Y') }}</p>
                <p><span class="font-bold text-gray-900">Against Invoice:</span> {{ $record->invoice?->friendly_id ?? '—' }}</p>
            </div>
        </div>

        @if ($record->reason)
            <p class="text-gray-700 mb-6"><span class="font-bold text-gray-900">Reason for Return:</span> {{ $record->reason }}</p>
        @endif

        <table class="w-full border-collapse mb-6">
            <thead>
                <tr class="bg-gray-900 text-white text-left text-xs uppercase">
                    <th class="py-2 px-3 rounded-l-md">#</th>
                    <th class="py-2 px-3">Product</th>
                    <th class="py-2 px-3 text-right">Qty</th>
                    <th class="py-2 px-3 text-right rounded-r-md">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->items as $i => $item)
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5 px-3 text-gray-500">{{ $i + 1 }}</td>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-gray-900">{{ $item->product?->description }}</span>
                            @if ($item->product?->code)
                                <span class="block text-xs text-gray-400">{{ $item->product->code }}</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-right text-gray-800">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }}</td>
                        <td class="py-2.5 px-3 text-right text-blue-800 font-medium">MVR {{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            // Items are stored pre-GST (mirroring InvoiceItem::amount), but
            // $record->value is the GST-inclusive amount actually refunded —
            // showing the gap as its own line (rather than only the total)
            // is what makes the printed rows add up to the printed total.
            $itemsSubtotal = round($record->items->sum('amount'), 2);
            $adjustment = round($record->value - $itemsSubtotal, 2);
            $gstPercent = $record->invoice?->gst_percent;
            $hasOrderDiscount = (float) ($record->invoice?->discount_value ?? 0) > 0;
        @endphp
        <div class="flex justify-end mb-8">
            <div class="w-64 space-y-1.5">
                <div class="flex justify-between"><span class="text-gray-700">Items Subtotal:</span><span class="text-blue-800">MVR {{ number_format($itemsSubtotal, 2) }}</span></div>
                @if ($adjustment != 0)
                    <div class="flex justify-between">
                        <span class="text-gray-700">{{ $hasOrderDiscount ? 'GST & Discount Adjustment:' : 'GST'.($gstPercent !== null ? ' ('.rtrim(rtrim(number_format($gstPercent, 2), '0'), '.').'%)' : '').':' }}</span>
                        <span class="text-blue-800">MVR {{ number_format($adjustment, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-bold text-base border-t-2 border-gray-900 pt-1.5"><span>Total Credited:</span><span>MVR {{ number_format($record->value, 2) }}</span></div>
            </div>
        </div>

        <div class="bg-gray-100 border border-gray-300 rounded-lg p-4 mb-8 text-xs text-gray-700">
            @if ($record->invoice)
                This credit note reduces the total of Invoice <span class="font-bold">{{ $record->invoice->friendly_id }}</span> by <span class="font-bold">MVR {{ number_format($record->value, 2) }}</span>.
                @if ($record->refund_applied_at)
                    Applied on {{ $record->refund_applied_at->format('F jS, Y') }}.
                @endif
            @else
                This credit note records a refund of <span class="font-bold">MVR {{ number_format($record->value, 2) }}</span> to the customer.
            @endif
        </div>

        <div class="grid grid-cols-2 gap-8 text-xs text-center">
            <div class="border-t border-gray-400 pt-2">
                <p class="font-semibold text-gray-800">Authorized By</p>
                <p class="text-gray-500 mt-0.5">{{ $record->createdBy?->name }}</p>
            </div>
            <div class="border-t border-gray-400 pt-2">
                <p class="font-semibold text-gray-800">Customer Acknowledgement</p>
                <p class="text-gray-500 mt-0.5">(Name, Signature)</p>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 italic mt-10">Thank you for your business!</p>
    </div>
</body>
</html>
