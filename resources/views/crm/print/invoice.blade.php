<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{{ $record->friendly_id }} · Favala</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A5;
            margin: 8mm;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: #fff !important; }
            .print-frame { border-width: 0 !important; box-shadow: none !important; width: 100% !important; padding: 0 !important; }
        }
        body { background: #f4f4f5; color: #111; }
        .print-frame, .print-frame * { line-height: 1.15; }
    </style>
</head>
<body class="p-6">
    <div class="no-print w-[148mm] mx-auto text-right mb-3">
        <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-accent text-white text-sm font-semibold">Print</button>
    </div>

    <div class="print-frame w-[148mm] mx-auto bg-white text-[10px] border-[3px] border-black rounded-xl p-4">
        @include('crm.print._header', ['title' => 'TAX INVOICE', 'compact' => true])

        <div class="grid grid-cols-2 gap-3 mb-2">
            <div>
                <p class="font-bold text-gray-900">Billed To:</p>
                <p class="text-gray-800">{{ $record->bill_to_name ?? $record->customer?->company_name }}</p>
                @if ($record->bill_to_phone)
                    <p class="text-gray-600">{{ $record->bill_to_phone }}</p>
                @endif
                @if ($record->customer?->tin)
                    <p class="text-gray-600">TIN: {{ $record->customer->tin }}</p>
                @endif
            </div>
            <div class="text-right">
                <p><span class="font-bold text-gray-900">Invoice #:</span> {{ $record->friendly_id }}</p>
                <p><span class="font-bold text-gray-900">Date:</span> {{ $record->invoice_date->format('d M Y') }}</p>
                <p><span class="font-bold text-gray-900">Expires:</span> {{ optional($record->expiry_date)->format('d M Y') }}</p>
                @if ($record->quotation)
                    <p><span class="font-bold text-gray-900">Quotation #:</span> {{ $record->quotation->friendly_id }}</p>
                @endif
                @if ($record->reference_number)
                    <p><span class="font-bold text-gray-900">Reference:</span> {{ $record->reference_number }}</p>
                @endif
            </div>
        </div>

        <table class="w-full border-collapse mb-2">
            <thead>
                <tr class="bg-gray-900 text-white text-left text-[9px] uppercase">
                    <th class="py-px px-1 rounded-l-md">#</th>
                    <th class="py-px px-1">Product</th>
                    <th class="py-px px-1 text-right">Qty</th>
                    <th class="py-px px-1 text-right">Rate</th>
                    <th class="py-px px-1 text-right">Disc.</th>
                    <th class="py-px px-1 text-right rounded-r-md">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php($refundedByProduct = $record->refundedByProduct())
                @foreach ($record->items as $i => $item)
                    @php($discountAmount = ($item->rate * $item->qty) - $item->amount)
                    @php($refunded = $refundedByProduct->get($item->product_id))
                    @php($refundedQty = $refunded['qty'] ?? 0)
                    @php($refundedAmount = $refunded['amount'] ?? 0)
                    @php($remainingQty = $item->qty - $refundedQty)
                    @php($remainingAmount = $item->amount - $refundedAmount)
                    <tr class="border-b border-gray-100">
                        <td class="py-px px-1 text-gray-500">{{ $i + 1 }}</td>
                        <td class="py-px px-1">
                            <span class="font-medium text-gray-900">{{ $item->product?->description }}</span>
                            @if ($item->product?->code)
                                <span class="block text-[8px] text-gray-400">{{ $item->product->code }}</span>
                            @endif
                        </td>
                        <td class="py-px px-1 text-right text-gray-800">
                            {{ rtrim(rtrim(number_format($remainingQty, 2), '0'), '.') }} {{ $item->product?->unit_of_measure }}
                            @if ($refundedQty > 0)
                                <span class="block text-[8px] text-red-500">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }} sold, {{ rtrim(rtrim(number_format($refundedQty, 2), '0'), '.') }} returned</span>
                            @endif
                        </td>
                        <td class="py-px px-1 text-right text-blue-800">{{ number_format($item->rate, 2) }}</td>
                        <td class="py-px px-1 text-right text-red-500">-{{ number_format($discountAmount, 2) }}</td>
                        <td class="py-px px-1 text-right text-blue-800 font-medium">
                            {{ number_format($remainingAmount, 2) }}
                            @if ($refundedQty > 0)
                                <span class="block text-[8px] text-gray-400 line-through">{{ number_format($item->amount, 2) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php($totals = $record->adjustedTotals())
        <div class="flex justify-end mb-2">
            <div class="w-36 space-y-0.5">
                <div class="flex justify-between">
                    <span class="text-gray-700">Sub Total:</span>
                    <span class="text-right">
                        <span class="text-blue-800 block">MVR {{ number_format($totals['subtotal'], 2) }}</span>
                        @if ($totals['refunded_value'] > 0)
                            <span class="block text-[8px] text-gray-400 line-through">MVR {{ number_format($record->subtotal, 2) }}</span>
                        @endif
                    </span>
                </div>
                @if ($totals['discount'] > 0)
                    <div class="flex justify-between"><span class="text-gray-700">Discount:</span><span class="text-red-500">-MVR {{ number_format($totals['discount'], 2) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-gray-700">GST ({{ rtrim(rtrim(number_format($record->gst_percent, 2), '0'), '.') }}%):</span><span class="text-blue-800">MVR {{ number_format($totals['gst'], 2) }}</span></div>
                @if ($totals['refunded_value'] > 0)
                    <div class="flex justify-between"><span class="text-gray-700">Refunded:</span><span class="text-red-500">-MVR {{ number_format($totals['refunded_value'], 2) }}</span></div>
                @endif
                <div class="flex justify-between font-bold text-[11px] border-t border-gray-300 pt-0.5">
                    <span>Total:</span>
                    <span class="text-right">
                        <span class="block">MVR {{ number_format($totals['grand_total'], 2) }}</span>
                        @if ($totals['refunded_value'] > 0)
                            <span class="block text-[8px] text-gray-400 font-normal line-through">MVR {{ number_format($record->grand_total, 2) }}</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between"><span class="text-gray-700">Paid:</span><span class="text-green-700">MVR {{ number_format($record->amount_paid, 2) }}</span></div>
                <div class="flex justify-between font-bold"><span>Balance Due:</span><span>MVR {{ number_format($totals['balance_due'], 2) }}</span></div>
            </div>
        </div>

        @if ($record->payments->isNotEmpty())
            <div class="mb-2">
                <p class="text-[9px] text-gray-500 uppercase font-semibold">Payment History</p>
                <table class="w-full text-[9px]">
                    <thead><tr class="border-b border-gray-300 text-left text-gray-600"><th class="py-px">Date</th><th class="py-px">Method</th><th class="py-px">Reference</th><th class="py-px text-right">Amount</th></tr></thead>
                    <tbody>
                        @foreach ($record->payments as $payment)
                            <tr><td class="py-px">{{ $payment->date->format('d M Y') }}</td><td class="py-px">{{ $payment->method }}</td><td class="py-px">{{ $payment->reference }}</td><td class="py-0.5 text-right">MVR {{ number_format($payment->amount, 2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @include('crm.print._terms', ['documentNoun' => 'invoice', 'compact' => true])
    </div>
</body>
</html>
