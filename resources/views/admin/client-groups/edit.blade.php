<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.client-groups.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('clients.edit_group') }}: {{ $group->name }}</h2>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">

        {{-- Group details --}}
        <form method="POST" action="{{ route('admin.client-groups.update', $group) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            @csrf
            @method('PUT')

            <h3 class="text-sm font-semibold text-gray-800">{{ __('clients.group_details') }}</h3>

            <x-input name="name" :label="__('common.name')" :value="old('name', $group->name)" required />

            <div>
                <label class="form-label" for="color">{{ __('clients.group_color') }}</label>
                <div class="mt-1 flex items-center gap-3">
                    <input type="color" name="color" id="color" value="{{ old('color', $group->color ?: '#6366f1') }}" class="h-10 w-16 rounded border-gray-300 cursor-pointer">
                    <span class="text-sm text-gray-500">{{ old('color', $group->color ?: '#6366f1') }}</span>
                </div>
            </div>

            <div>
                <label class="form-label">{{ __('common.description') }}</label>
                <textarea name="description" rows="2" class="form-input">{{ old('description', $group->description) }}</textarea>
            </div>

            <div>
                <x-input name="discount_percent" type="number" step="0.01" min="0" max="100"
                    :label="__('clients.discount_percent')"
                    :value="old('discount_percent', number_format($group->discount_percent / 100, 2))" />
                <p class="mt-1 text-xs text-gray-400">{{ __('clients.discount_percent_help') }}</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
                <a href="{{ route('admin.client-groups.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>

        {{-- Per-product price overrides --}}
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">{{ __('clients.price_overrides') }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ __('clients.price_overrides_help') }}</p>
            </div>

            @if ($group->pricing->isNotEmpty())
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('products.product') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('common.currency') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('products.billing_cycle') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('products.price') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('products.setup_fee') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($group->pricing as $price)
                                <tr>
                                    <td class="px-4 py-2 text-gray-800">{{ $price->product?->name ?? '—' }}</td>
                                    <td class="px-4 py-2 text-gray-600 font-mono">{{ $price->currency_code }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ __('store.cycles.' . $price->billing_cycle) }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900">{{ number_format($price->price / 100, 2) }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ number_format($price->setup_fee / 100, 2) }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <form method="POST" action="{{ route('admin.client-groups.prices.destroy', [$group, $price]) }}" class="inline"
                                              onsubmit="return confirm('{{ __('common.are_you_sure') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs">{{ __('common.remove') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-400 italic">{{ __('clients.no_price_overrides') }}</p>
            @endif

            {{-- Add override form --}}
            <form method="POST" action="{{ route('admin.client-groups.prices.store', $group) }}" class="border-t border-gray-100 pt-5 space-y-4">
                @csrf
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('clients.add_price_override') }}</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="form-label text-xs">{{ __('products.product') }}</label>
                        <select name="product_id" class="form-input" required>
                            <option value="">—</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">{{ __('common.currency') }}</label>
                        <select name="currency_code" class="form-input" required>
                            @foreach ($currencies as $c)
                                <option value="{{ $c->code }}" {{ $c->is_default ? 'selected' : '' }}>{{ $c->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">{{ __('products.billing_cycle') }}</label>
                        <select name="billing_cycle" class="form-input" required>
                            @foreach ($cycles as $c)
                                <option value="{{ $c }}">{{ __('store.cycles.' . $c) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs">{{ __('products.price') }}</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-input" required placeholder="0.00">
                    </div>
                    <div>
                        <label class="form-label text-xs">{{ __('products.setup_fee') }}</label>
                        <input type="number" step="0.01" min="0" name="setup_fee" class="form-input" placeholder="0.00">
                    </div>
                </div>

                <button type="submit" class="btn-secondary text-sm">+ {{ __('clients.add_price_override') }}</button>
            </form>
        </div>

    </div>
</x-admin-layout>
