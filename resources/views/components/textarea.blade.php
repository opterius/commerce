@props([
    'name',
    'value' => '',
    'label' => null,
    'rows' => 3,
])

<div>
    @if ($label)
        <x-label :for="$name" :value="$label" />
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->merge([
            'class' => 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm'
        ]) }}
    >{{ old($name, $value) }}</textarea>

    <x-input-error :messages="$errors->get($name)" />
</div>
