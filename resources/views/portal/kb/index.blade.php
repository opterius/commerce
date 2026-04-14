<x-portal-layout>

    {{-- Header --}}
    <section class="portal-hero" style="padding: 4rem 1.5rem;">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-white font-extrabold" style="font-size: clamp(1.75rem, 4vw, 2.5rem)">{{ __('kb.portal_title') }}</h1>
            <p style="color: rgba(255,255,255,.78); margin-top: .75rem; font-size: 1rem;">{{ __('kb.portal_subtitle') }}</p>

            <form method="GET" action="{{ route('portal.kb') }}" class="mt-8 max-w-xl mx-auto">
                <div style="display:flex; background:#fff; border-radius:.75rem; overflow:hidden; box-shadow: 0 4px 12px rgba(0,0,0,.08);">
                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        placeholder="{{ __('kb.search_placeholder') }}"
                        style="flex:1; padding: .875rem 1.25rem; font-size: .9375rem; border: 0; outline: none;"
                    >
                    <button type="submit" class="portal-search-btn" style="border-radius: 0;">
                        {{ __('common.search') }}
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="max-w-5xl mx-auto" style="padding: 3rem 1.5rem;">

        {{-- Search results --}}
        @if ($searchResults !== null)
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ __('kb.search_results_for', ['query' => $query]) }}
                    <span class="text-sm font-normal text-gray-400 ml-2">({{ $searchResults->count() }})</span>
                </h2>

                @if ($searchResults->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('kb.no_results') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach ($searchResults as $article)
                            <a href="{{ route('portal.kb.article', [$article->category, $article]) }}"
                               class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-300 hover:shadow-sm transition">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $article->title }}</h3>
                                        @if ($article->excerpt)
                                            <p class="text-sm text-gray-500 mt-1 leading-relaxed">{{ Str::limit($article->excerpt, 140) }}</p>
                                        @endif
                                    </div>
                                    @if ($article->category)
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full whitespace-nowrap">{{ $article->category->name }}</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="border-t border-gray-100 pt-8"></div>
        @endif

        {{-- Categories grid --}}
        @if ($categories->isEmpty())
            <div class="text-center py-12">
                <p class="text-sm text-gray-400">{{ __('kb.no_categories') }}</p>
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                @foreach ($categories as $cat)
                    <a href="{{ route('portal.kb.category', $cat) }}"
                       class="block bg-white rounded-xl border border-gray-200 p-6 hover:border-indigo-300 hover:shadow-md transition">
                        <h3 class="font-semibold text-gray-900 text-base mb-1">{{ $cat->name }}</h3>
                        @if ($cat->description)
                            <p class="text-sm text-gray-500 mb-4 leading-relaxed">{{ Str::limit($cat->description, 100) }}</p>
                        @else
                            <div class="mb-4"></div>
                        @endif

                        @if ($cat->publishedArticles->isNotEmpty())
                            <ul class="space-y-1.5 text-sm">
                                @foreach ($cat->publishedArticles as $article)
                                    <li class="text-gray-600 truncate">{{ $article->title }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <p class="text-xs font-semibold mt-4 portal-accent-text" style="color: var(--pa)">
                            {{ __('kb.browse_category') }} →
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

</x-portal-layout>
