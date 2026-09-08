{{--
    Single-series vertical bar chart — dataviz skill: one hue, baseline at zero,
    rounded bar caps, hairline gridlines, sparing direct labels, native hover tooltip.
    No JS dependency — pure server-rendered SVG, consistent with this app's
    "reduce external CDN coupling" requirement.

    Note: every assignment below uses single-line @php(...) instead of a
    @php ... @endphp block. A ternary inside a multi-line @php block, especially
    one nested inside a @foreach, reliably breaks this app's Blade compiler — it
    drops the opening PHP tag. Keep it that way; see the Targets-page fix for precedent.
--}}
@props(['points', 'money' => true, 'color' => 'var(--color-accent)', 'height' => 180])

@php($max = collect($points)->max('value') ?: 1)
@php($count = max(count($points), 1))
@php($barWidth = 24)
@php($gap = max(16, 200 / $count))
@php($chartWidth = $count * ($barWidth + $gap))
@php($labelHeight = 22)
@php($valueHeight = 18)
@php($plotHeight = $height - $labelHeight - $valueHeight)

<div class="overflow-x-auto">
    <svg viewBox="0 0 {{ $chartWidth }} {{ $height }}" class="w-full" preserveAspectRatio="xMinYMid meet" style="min-width: {{ $chartWidth }}px; height: {{ $height }}px;">
        @foreach ([0, 0.5, 1] as $frac)
            <line x1="0" y1="{{ $valueHeight + $plotHeight * (1 - $frac) }}" x2="{{ $chartWidth }}" y2="{{ $valueHeight + $plotHeight * (1 - $frac) }}"
                stroke="rgba(255,255,255,0.08)" stroke-width="1" />
        @endforeach

        @foreach ($points as $i => $point)
            @php($x = $i * ($barWidth + $gap) + $gap / 2)
            @php($rawHeight = $max > 0 ? ($point['value'] / $max) * $plotHeight : 0)
            @php($barHeight = max($rawHeight, 2))
            @php($y = $valueHeight + $plotHeight - $barHeight)
            @php($displayValue = $money ? ($point['value'] >= 1000 ? number_format($point['value'] / 1000, 1).'K' : number_format($point['value'], 0)) : number_format($point['value'], 0))
            <g>
                <title>{{ $point['label'] }}: {{ $money ? 'MVR '.number_format($point['value'], 2) : number_format($point['value']) }}</title>
                @if ($barHeight >= 8)
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barWidth }}" height="{{ $barHeight }}" rx="4" fill="{{ $color }}" />
                    <rect x="{{ $x }}" y="{{ $y + $barHeight - 4 }}" width="{{ $barWidth }}" height="4" fill="{{ $color }}" />
                @else
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barWidth }}" height="{{ $barHeight }}" rx="1" fill="{{ $color }}" />
                @endif
                <text x="{{ $x + $barWidth / 2 }}" y="{{ max($y - 6, 11) }}" text-anchor="middle" font-size="10" font-weight="600" fill="#e4e4e7">{{ $displayValue }}</text>
                <text x="{{ $x + $barWidth / 2 }}" y="{{ $height - 6 }}" text-anchor="middle" font-size="10" fill="#71717a">{{ $point['label'] }}</text>
            </g>
        @endforeach
    </svg>
</div>
