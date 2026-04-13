<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center justify-center gap-2 rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed',
]) }}>
    {{ $slot }}
</button>
