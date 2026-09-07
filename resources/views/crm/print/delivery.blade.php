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
        @include('crm.print._header', ['title' => 'DELIVERY NOTE'])

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <p class="font-bold text-gray-900 mb-1">Deliver To:</p>
                <p class="text-gray-800">{{ $record->customer?->company_name }}</p>
                <p class="text-gray-600">{{ $record->contact_name }}{{ $record->contact_phone ? ' — '.$record->contact_phone : '' }}</p>
                <p class="text-gray-600">{{ $record->location ?: '-' }}</p>
            </div>
            <div class="text-right space-y-0.5">
                <p><span class="font-bold text-gray-900">Delivery #:</span> {{ $record->friendly_id }}</p>
                <p><span class="font-bold text-gray-900">Invoice #:</span> {{ $record->invoice?->friendly_id }}</p>
                <p><span class="font-bold text-gray-900">Deadline:</span> {{ optional($record->deadline_date)->format('F jS, Y') }} {{ $record->deadline_time }}</p>
            </div>
        </div>

        <table class="w-full border-collapse mb-8">
            <thead>
                <tr class="bg-gray-900 text-white text-left text-xs uppercase">
                    <th class="py-2 px-3 rounded-l-md">#</th>
                    <th class="py-2 px-3">Product</th>
                    <th class="py-2 px-3 text-right rounded-r-md">Delivery Qty</th>
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
                        <td class="py-2.5 px-3 text-right text-blue-800 font-medium">{{ rtrim(rtrim(number_format($item->delivery_qty, 2), '0'), '.') }} {{ $item->product?->unit_of_measure }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="grid grid-cols-2 gap-8 mt-16 mb-8 text-xs">
            <div class="border-t border-gray-400 pt-2">Delivered By</div>
            <div class="border-t border-gray-400 pt-2">Received By</div>
        </div>

        @include('crm.print._terms', ['documentNoun' => 'delivery note'])
    </div>
</body>
</html>
