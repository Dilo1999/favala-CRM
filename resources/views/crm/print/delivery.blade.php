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
        <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-orange-600 text-white text-sm font-semibold">Print</button>
    </div>

    <div class="flex items-start justify-between mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <div class="h-8 w-8 rounded-lg bg-orange-600 flex items-center justify-center font-bold text-white">F</div>
                <span class="font-bold text-lg">FAVALA</span>
            </div>
            <p class="text-gray-500 text-xs">Construction & Hardware Supply — Maldives</p>
        </div>
        <div class="text-right">
            <h1 class="text-xl font-bold">DELIVERY NOTE</h1>
            <p class="text-gray-600">{{ $record->friendly_id }}</p>
            <p class="text-gray-500 text-xs">Invoice: {{ $record->invoice?->friendly_id }}</p>
            <p class="text-gray-500 text-xs">Deadline: {{ optional($record->deadline_date)->format('d M Y') }} {{ $record->deadline_time }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-8">
        <div>
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Deliver To</p>
            <p class="font-medium">{{ $record->customer?->company_name }}</p>
            <p class="text-gray-600">{{ $record->contact_name }} — {{ $record->contact_phone }}</p>
            <p class="text-gray-600">{{ $record->location }}</p>
        </div>
    </div>

    <table class="w-full border-collapse mb-8">
        <thead>
            <tr class="border-b-2 border-gray-800 text-left text-xs uppercase text-gray-600">
                <th class="py-2">#</th><th class="py-2">Product</th><th class="py-2 text-right">Delivery Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($record->items as $i => $item)
                <tr class="border-b border-gray-200">
                    <td class="py-2">{{ $i + 1 }}</td>
                    <td class="py-2">{{ $item->product?->description }}</td>
                    <td class="py-2 text-right">{{ $item->delivery_qty }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="grid grid-cols-2 gap-8 mt-16 text-xs">
        <div class="border-t border-gray-400 pt-2">Delivered By</div>
        <div class="border-t border-gray-400 pt-2">Received By</div>
    </div>
</body>
</html>
