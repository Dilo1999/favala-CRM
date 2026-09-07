<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{{ $record->friendly_id }} · Favala</title>
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
        @include('crm.print._header', ['title' => 'QUOTATION'])

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <p class="font-bold text-gray-900 mb-1">Bill To:</p>
                <p class="text-gray-800">{{ $record->bill_to_name ?? $record->customer?->company_name }}</p>
                @if ($record->bill_to_phone)
                    <p class="text-gray-600">{{ $record->bill_to_phone }}</p>
                @endif
                <p class="text-gray-600">{{ $record->bill_to_address ?: '-' }}</p>
            </div>
            <div class="text-right space-y-0.5">
                <p><span class="font-bold text-gray-900">Quotation #:</span> {{ $record->friendly_id }}</p>
                <p><span class="font-bold text-gray-900">Date:</span> {{ $record->quotation_date->format('F jS, Y') }}</p>
                <p><span class="font-bold text-gray-900">Expires:</span> {{ optional($record->expiry_date)->format('F jS, Y') }}</p>
            </div>
        </div>

        <table class="w-full border-collapse mb-6">
            <thead>
                <tr class="bg-gray-900 text-white text-left text-xs uppercase">
                    <th class="py-2 px-3 rounded-l-md">#</th>
                    <th class="py-2 px-3">Product</th>
                    <th class="py-2 px-3 text-right">Qty</th>
                    <th class="py-2 px-3 text-right">Rate</th>
                    <th class="py-2 px-3 text-right">Discount</th>
                    <th class="py-2 px-3 text-right rounded-r-md">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->items as $i => $item)
                    @php($discountAmount = ($item->unit_price * $item->qty) - $item->line_amount)
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5 px-3 text-gray-500">{{ $i + 1 }}</td>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-gray-900">{{ $item->product?->description }}</span>
                            @if ($item->product?->code)
                                <span class="block text-xs text-gray-400">{{ $item->product->code }}</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-right text-gray-800">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }} {{ $item->product?->unit_of_measure }}</td>
                        <td class="py-2.5 px-3 text-right text-blue-800">MVR {{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-2.5 px-3 text-right text-red-500">-MVR {{ number_format($discountAmount, 2) }}</td>
                        <td class="py-2.5 px-3 text-right text-blue-800 font-medium">MVR {{ number_format($item->line_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-end mb-4">
            <div class="w-64 space-y-1.5">
                <div class="flex justify-between"><span class="text-gray-700">Sub Total:</span><span class="text-blue-800">MVR {{ number_format($record->subtotal, 2) }}</span></div>
                @if ($record->discount_value > 0)
                    <div class="flex justify-between"><span class="text-gray-700">Discount:</span><span class="text-red-500">-MVR {{ number_format($record->subtotal - ($record->grand_total - $record->gst_amount), 2) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-gray-700">Tax (GST {{ rtrim(rtrim(number_format($record->gst_percent, 2), '0'), '.') }}%):</span><span class="text-blue-800">MVR {{ number_format($record->gst_amount, 2) }}</span></div>
                <div class="flex justify-between font-bold text-base border-t border-gray-300 pt-1.5"><span>Grand Total:</span><span>MVR {{ number_format($record->grand_total, 2) }}</span></div>
            </div>
        </div>

        @include('crm.print._terms', ['documentNoun' => 'quotation'])
    </div>
</body>
</html>
