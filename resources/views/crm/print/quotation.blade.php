<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{{ $record->friendly_id }} · Favala</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print { .no-print { display: none !important; } body { padding: 0 !important; } }
        body { background: #fff; color: #111; }
    </style>
</head>
<body class="p-10 max-w-3xl mx-auto text-sm">
    <div class="no-print text-right mb-4">
        <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-accent text-white text-sm font-semibold">Print</button>
    </div>

    <div class="flex items-start justify-between mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <div class="h-8 w-8 rounded-lg bg-accent flex items-center justify-center font-bold text-white">F</div>
                <span class="font-bold text-lg">FAVALA</span>
            </div>
            <p class="text-gray-500 text-xs">Construction & Hardware Supply — Maldives</p>
        </div>
        <div class="text-right">
            <h1 class="text-xl font-bold">QUOTATION</h1>
            <p class="text-gray-600">{{ $record->friendly_id }}</p>
            <p class="text-gray-500 text-xs">Date: {{ $record->quotation_date->format('d M Y') }}</p>
            <p class="text-gray-500 text-xs">Expiry: {{ optional($record->expiry_date)->format('d M Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-8">
        <div>
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Bill To</p>
            <p class="font-medium">{{ $record->bill_to_name ?? $record->customer?->company_name }}</p>
            <p class="text-gray-600">{{ $record->bill_to_phone }}</p>
            <p class="text-gray-600">{{ $record->bill_to_address }}</p>
        </div>
    </div>

    <table class="w-full border-collapse mb-8">
        <thead>
            <tr class="border-b-2 border-gray-800 text-left text-xs uppercase text-gray-600">
                <th class="py-2">#</th><th class="py-2">Product</th><th class="py-2 text-right">Qty</th><th class="py-2 text-right">Rate</th><th class="py-2 text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($record->items as $i => $item)
                <tr class="border-b border-gray-200">
                    <td class="py-2">{{ $i + 1 }}</td>
                    <td class="py-2">{{ $item->product?->description }} <span class="text-gray-400 text-xs">{{ $item->product?->code }}</span></td>
                    <td class="py-2 text-right">{{ $item->qty }}</td>
                    <td class="py-2 text-right">MVR {{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-2 text-right">MVR {{ number_format($item->line_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="flex justify-end mb-8">
        <div class="w-64 space-y-1">
            <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span>MVR {{ number_format($record->subtotal, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-600">GST ({{ $record->gst_percent }}%)</span><span>MVR {{ number_format($record->gst_amount, 2) }}</span></div>
            <div class="flex justify-between font-bold text-base border-t border-gray-800 pt-1"><span>Grand Total</span><span>MVR {{ number_format($record->grand_total, 2) }}</span></div>
        </div>
    </div>

    <p class="text-xs text-gray-400 border-t border-gray-200 pt-4">This quotation is valid until the expiry date shown above. Prices include applicable GST as stated.</p>
</body>
</html>
