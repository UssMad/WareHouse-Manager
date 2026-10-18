@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-5 border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800']) }}>
        {{ $status }}
    </div>
@endif
