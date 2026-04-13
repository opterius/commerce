<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-6">{{ __('settings.branding') }}</h3>

    <form method="POST" action="{{ route('admin.settings.update', 'branding') }}" enctype="multipart/form-data">
        @csrf

        <div class="space-y-4">
            <div>
                <x-input name="brand_name" :label="__('settings.brand_name')" :value="$settings['brand_name'] ?? ''" />
                <p class="mt-1 text-xs text-gray-400">{{ __('settings.brand_name_help') }}</p>
            </div>

            <div>
                <x-label for="brand_logo" :value="__('settings.brand_logo')" />
                @if (!empty($settings['brand_logo']))
                    <div class="mt-1 mb-2">
                        <img src="{{ asset('storage/' . $settings['brand_logo']) }}" alt="{{ __('settings.brand_logo') }}" class="h-10">
                    </div>
                @endif
                <input
                    type="file"
                    name="brand_logo"
                    id="brand_logo"
                    accept="image/*"
                    class="mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
                <p class="mt-1 text-xs text-gray-400">{{ __('settings.brand_logo_help') }}</p>
                <x-input-error :messages="$errors->get('brand_logo')" />
            </div>

            <div>
                <x-label for="brand_favicon" :value="__('settings.brand_favicon')" />
                <input
                    type="file"
                    name="brand_favicon"
                    id="brand_favicon"
                    accept="image/*"
                    class="mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
                <x-input-error :messages="$errors->get('brand_favicon')" />
            </div>

            <div>
                <x-label for="brand_primary_color" :value="__('settings.brand_primary_color')" />
                <div class="mt-1 flex items-center gap-3">
                    <input
                        type="color"
                        name="brand_primary_color"
                        id="brand_primary_color"
                        value="{{ $settings['brand_primary_color'] ?? '#4f46e5' }}"
                        class="h-10 w-16 rounded border-gray-300 cursor-pointer"
                    />
                    <span class="text-sm text-gray-500">{{ $settings['brand_primary_color'] ?? '#4f46e5' }}</span>
                </div>
                <x-input-error :messages="$errors->get('brand_primary_color')" />
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-button>{{ __('common.save_changes') }}</x-button>
        </div>
    </form>
</div>
