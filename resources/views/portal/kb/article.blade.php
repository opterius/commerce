<x-portal-layout>

    <article class="max-w-3xl mx-auto" style="padding: 3rem 1.5rem;">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('portal.kb') }}" class="hover:text-gray-700">{{ __('kb.portal_title') }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('portal.kb.category', $category) }}" class="hover:text-gray-700">{{ $category->name }}</a>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900 mb-3 leading-tight">{{ $article->title }}</h1>

        <div class="flex items-center gap-4 text-xs text-gray-400 mb-8">
            <span>{{ __('kb.views') }}: {{ number_format($article->views) }}</span>
            <span>·</span>
            <span>{{ __('kb.updated') }}: {{ $article->updated_at->diffForHumans() }}</span>
        </div>

        <div class="prose max-w-none text-gray-700 leading-relaxed" style="font-size: 1rem; white-space: pre-wrap;">{!! nl2br(e($article->content)) !!}</div>

        {{-- Related --}}
        @if ($related->isNotEmpty())
            <div class="mt-16 pt-8 border-t border-gray-200">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('kb.related_articles') }}</h2>
                <div class="space-y-2">
                    @foreach ($related as $rel)
                        <a href="{{ route('portal.kb.article', [$category, $rel]) }}"
                           class="block text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
                            {{ $rel->title }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-10">
            <a href="{{ route('portal.kb.category', $category) }}" class="text-sm text-gray-500 hover:text-gray-800">← {{ __('kb.back_to_category', ['name' => $category->name]) }}</a>
        </div>

    </article>

</x-portal-layout>
