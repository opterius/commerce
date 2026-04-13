@props([
    'name',
    'options' => [],
    'selected' => null,
    'label' => null,
    'required' => false,
])

<div>
    @if ($label)
        <x-label :for="$name" :value="$label" />
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge([
            'class' => 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm'
        ]) }}
    >
        @foreach ($options as $optionValue => $optionLabel)
            <option
                value="{{ $optionValue }}"
                {{ old($name, $selected) == $optionValue ? 'selected' : '' }}
            >
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    <x-input-error :messages="$errors->get($name)" />
</div>
