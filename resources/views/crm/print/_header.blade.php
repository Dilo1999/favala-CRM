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

<div class="bg-blue-100 text-center py-3 rounded mb-6">
    <h2 class="text-xl font-bold tracking-widest text-blue-950">{{ $title }}</h2>
</div>
