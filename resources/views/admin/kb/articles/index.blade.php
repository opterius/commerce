<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('kb.articles') }}</h2>
            <a href="{{ route('admin.kb-articles.create') }}" class="btn-primary">{{ __('kb.create_article') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Filters --}}
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap items-center gap-3">
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="{{ __('common.search') }}"
                   class="form-input max-w-sm">
            <select name="category" class="form-input max-w-xs">
                <option value="">— {{ __('kb.all_categories') }} —</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">{{ __('common.filter') }}</button>
        </form>

        @if ($articles->isEmpty())
            <x-empty-state :message="__('kb.no_articles')" />
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('kb.title') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('kb.category') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('kb.views') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($articles as $article)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.kb-articles.edit', $article) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $article->title }}
                                    </a>
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $article->slug }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $article->category?->name ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($article->views) }}</td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$article->is_published ? 'green' : 'gray'">
                                        {{ $article->is_published ? __('kb.published') : __('kb.draft') }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.kb-articles.edit', $article) }}" class="btn-secondary py-1 px-3 text-xs">{{ __('common.edit') }}</a>
                                        <x-delete-modal
                                            :action="route('admin.kb-articles.destroy', $article)"
                                            label="{{ __('common.delete') }}"
                                            :confirmMessage="__('common.are_you_sure')"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>{{ $articles->links() }}</div>
        @endif
    </div>
</x-admin-layout>
