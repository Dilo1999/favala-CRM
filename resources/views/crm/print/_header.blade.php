@php($company = config('crm.company'))
@php($compact = $compact ?? false)

<div class="flex items-start justify-between {{ $compact ? 'mb-1.5' : 'mb-4' }}">
    <img src="{{ asset('images/logo/Favala-1.png') }}" alt="{{ $company['name'] }}" class="h-10 w-auto object-contain" />
    <div class="text-right">
        <h1 class="{{ $compact ? 'text-sm' : 'text-3xl' }} font-bold tracking-tight text-gray-900">{{ $company['name'] }}</h1>
        @foreach ($company['address_lines'] as $line)
            <p class="{{ $compact ? 'text-[8px]' : 'text-xs' }} text-blue-800 leading-tight">{{ $line }}</p>
        @endforeach
        <p class="{{ $compact ? 'text-[8px]' : 'text-xs mt-1' }} text-gray-700">{{ implode(' | ', $company['phones']) }}</p>
        <p class="{{ $compact ? 'text-[8px]' : 'text-xs' }} text-blue-800">{{ implode(' | ', $company['emails']) }}</p>
        <p class="{{ $compact ? 'text-[8px]' : 'text-xs' }} text-gray-500">{{ $company['gst_number'] }}</p>
    </div>
</div>

<div class="bg-blue-100 text-center rounded {{ $compact ? 'py-1 mb-2' : 'py-3 mb-6' }}">
    <h2 class="{{ $compact ? 'text-xs' : 'text-xl' }} font-bold tracking-widest text-blue-950">{{ $title }}</h2>
</div>
