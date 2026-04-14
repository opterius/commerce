@php
    $accentColor = $settings['portal_primary_color'] ?? '#4f46e5';
    $existingLinks = json_decode($settings['portal_nav_links'] ?? '[]', true) ?? [];
@endphp

<form method="POST" action="{{ route('admin.settings.update', 'portal') }}" class="space-y-6">
    @csrf

    {{-- Appearance --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-5">{{ __('settings.portal_appearance') }}</h3>
        <div class="space-y-5">
            <div>
                <x-input name="portal_hero_title" :label="__('settings.portal_hero_title')"
                    :value="$settings['portal_hero_title'] ?? ''" placeholder="Hosting that just works." />
                <p class="mt-1 text-xs text-gray-400">{{ __('settings.portal_hero_title_help') }}</p>
            </div>
            <div>
                <label class="form-label">{{ __('settings.portal_hero_subtitle') }}</label>
                <textarea name="portal_hero_subtitle" rows="2"
                    class="form-input mt-1" placeholder="Choose a plan below to get started.">{{ $settings['portal_hero_subtitle'] ?? '' }}</textarea>
                <p class="mt-1 text-xs text-gray-400">{{ __('settings.portal_hero_subtitle_help') }}</p>
            </div>
            <div>
                <label class="form-label" for="portal_primary_color">{{ __('settings.portal_primary_color') }}</label>
                <div class="mt-1 flex items-center gap-3">
                    <input type="color" name="portal_primary_color" id="portal_primary_color"
                        value="{{ $accentColor }}"
                        class="h-10 w-16 rounded border-gray-300 cursor-pointer" />
                    <span class="text-sm text-gray-500">{{ $accentColor }}</span>
                </div>
                <p class="mt-1 text-xs text-gray-400">{{ __('settings.portal_primary_color_help') }}</p>
            </div>
        </div>
    </div>

    {{-- Navigation Links --}}
    <div class="bg-white rounded-xl shadow-sm p-6"
         x-data="{
             links: {{ json_encode(array_values($existingLinks)) }},
             addLink() { this.links.push({ label: '', url: '', open_new: false }) },
             removeLink(i) { this.links.splice(i, 1) },
         }">

        <h3 class="text-base font-semibold text-gray-800 mb-1">{{ __('settings.portal_navigation') }}</h3>
        <p class="text-sm text-gray-500 mb-5">{{ __('settings.portal_nav_links_help') }}</p>

        <input type="hidden" name="portal_nav_links" :value="JSON.stringify(links)">

        <div class="space-y-2">
            <template x-for="(link, i) in links" :key="i">
                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        x-model="links[i].label"
                        placeholder="{{ __('settings.portal_nav_label') }}"
                        class="form-input w-36"
                    >
                    <input
                        type="text"
                        x-model="links[i].url"
                        placeholder="{{ __('settings.portal_nav_url') }}"
                        class="form-input flex-1"
                    >
                    <label class="flex items-center gap-1.5 text-xs text-gray-500 whitespace-nowrap shrink-0">
                        <input type="checkbox" x-model="links[i].open_new" class="rounded border-gray-300">
                        {{ __('settings.portal_nav_new_tab') }}
                    </label>
                    <button type="button" @click="removeLink(i)"
                        class="shrink-0 text-gray-300 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>

            <div x-show="links.length === 0" class="text-sm text-gray-400 py-2">
                {{ __('settings.portal_nav_empty') }}
            </div>
        </div>

        <button type="button" @click="addLink"
            class="mt-4 btn-secondary text-sm">
            + {{ __('settings.portal_nav_add') }}
        </button>
    </div>

    {{-- Sections --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-1">{{ __('settings.portal_sections') }}</h3>
        <p class="text-sm text-gray-500 mb-5">{{ __('settings.portal_sections_help') }}</p>

        <div class="space-y-3">
            @php
                $sections = [
                    ['key' => 'portal_show_hero',          'label' => __('settings.portal_show_hero'),          'desc' => __('settings.portal_show_hero_help'),          'default' => '1'],
                    ['key' => 'portal_show_products',       'label' => __('settings.portal_show_products'),       'desc' => __('settings.portal_show_products_help'),       'default' => '1'],
                    ['key' => 'portal_show_domain_search',  'label' => __('settings.portal_show_domain_search'),  'desc' => __('settings.portal_show_domain_search_help'),  'default' => '0'],
                    ['key' => 'portal_show_kb',             'label' => __('settings.portal_show_kb'),             'desc' => __('settings.portal_show_kb_help'),             'default' => '0'],
                    ['key' => 'portal_show_faq',            'label' => __('settings.portal_show_faq'),            'desc' => __('settings.portal_show_faq_help'),            'default' => '0'],
                    ['key' => 'portal_show_contact',        'label' => __('settings.portal_show_contact'),        'desc' => __('settings.portal_show_contact_help'),        'default' => '0'],
                ];
            @endphp

            @foreach ($sections as $section)
                <label class="flex items-start gap-3 cursor-pointer group">
                    <div class="flex items-center h-5 mt-0.5">
                        <input type="hidden" name="{{ $section['key'] }}" value="0">
                        <input
                            type="checkbox"
                            name="{{ $section['key'] }}"
                            value="1"
                            {{ ($settings[$section['key']] ?? $section['default']) === '1' ? 'checked' : '' }}
                            class="rounded border-gray-300 text-indigo-600"
                        >
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 group-hover:text-gray-900">{{ $section['label'] }}</p>
                        <p class="text-xs text-gray-400">{{ $section['desc'] }}</p>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
    </div>

</form>
