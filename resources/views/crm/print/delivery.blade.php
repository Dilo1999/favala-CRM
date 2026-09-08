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
        @php($company = config('crm.company'))
        <div class="flex items-start justify-between mb-4">
            <img src="{{ asset('images/logo/Favala-1.png') }}" alt="{{ $company['name'] }}" class="h-10 w-auto object-contain" />
            <div class="text-right">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $company['name'] }}</h1>
                @foreach ($company['address_lines'] as $line)
                    <p class="text-xs text-blue-800 leading-tight">{{ $line }}</p>
                @endforeach
                <p class="text-xs text-gray-700 mt-1">{{ implode(' | ', $company['phones']) }}</p>
                <p class="text-xs text-blue-800">{{ implode(' | ', $company['emails']) }}</p>
                <p class="text-xs text-gray-500">{{ $company['gst_number'] }}</p>
            </div>
        </div>

        <div class="bg-gray-100 border-y-2 border-black text-center py-3 mb-6">
            <h2 class="text-xl font-bold tracking-widest text-gray-900">DELIVERY NOTE</h2>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <p class="font-bold text-gray-900 mb-1">To:</p>
                <p class="text-gray-900 font-medium">{{ $record->customer?->company_name }}</p>
                @if ($record->location)
                    <p class="text-gray-600 whitespace-pre-line">{{ $record->location }}</p>
                @endif
                @if ($record->contact_name || $record->contact_phone)
                    <p class="text-gray-600 mt-1">Contact: {{ trim(($record->contact_name ?? '').($record->contact_name && $record->contact_phone ? ', ' : '').($record->contact_phone ?? ''), ', ') }}</p>
                @endif
            </div>
            <div class="text-right space-y-0.5">
                <p><span class="font-bold text-gray-900">Delivery Note #:</span> {{ $record->friendly_id }}</p>
                <p><span class="font-bold text-gray-900">Date:</span> {{ $record->created_at->format('F jS, Y') }}</p>
                <p><span class="font-bold text-gray-900">Invoice #:</span> {{ $record->invoice?->friendly_id }}</p>
                <p class="flex items-center justify-end gap-2">
                    <span class="font-bold text-gray-900">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $record->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ ucfirst($record->status) }}
                    </span>
                </p>
            </div>
        </div>

        <table class="w-full border-collapse mb-10">
            <thead>
                <tr class="bg-gray-100 border-b-2 border-black text-left text-xs uppercase text-gray-700">
                    <th class="py-2 px-3">#</th>
                    <th class="py-2 px-3">Item Description</th>
                    <th class="py-2 px-3 text-right">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->items as $i => $item)
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5 px-3 text-gray-500">{{ $i + 1 }}</td>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-gray-900">{{ $item->product?->description }}</span>
                            @if ($item->product?->code)
                                <span class="block text-xs text-blue-700">{{ $item->product->code }}</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-right text-gray-900 font-medium">{{ rtrim(rtrim(number_format($item->delivery_qty, 2), '0'), '.') }} {{ $item->product?->unit_of_measure }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="grid grid-cols-3 gap-8 mb-8 text-xs text-center">
            <div class="border-t border-gray-400 pt-2">
                <p class="font-semibold text-gray-800">Prepared By</p>
                <p class="text-gray-500 mt-0.5">{{ $record->createdBy?->name }}</p>
            </div>
            <div class="border-t border-gray-400 pt-2">
                <p class="font-semibold text-gray-800">Delivered By</p>
            </div>
            <div class="border-t border-gray-400 pt-2">
                <p class="font-semibold text-gray-800">Received By</p>
                <p class="text-gray-500 mt-0.5">(Name, Signature, Company Stamp)</p>
            </div>
        </div>

        <p class="text-center text-xs font-semibold text-accent">Thank you for your business!</p>
    </div>
</body>
</html>
