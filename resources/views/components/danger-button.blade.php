<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center border border-red-600 bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
