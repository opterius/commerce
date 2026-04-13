@props([
    'name',
    'type' => 'text',
    'value' => '',
    'label' => null,
    'required' => false,
    'placeholder' => '',
])

<div>
    @if ($label)
        <x-label :for="$name" :value="$label" />
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge([
            'class' => 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm'
        ]) }}
    />

    <x-input-error :messages="$errors->get($name)" />
</div>
