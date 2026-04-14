<x-portal-layout>

    <section class="portal-hero" style="padding: 3rem 1.5rem;">
        <div class="max-w-4xl mx-auto text-center">
            <p style="color: rgba(255,255,255,.6); font-size: .8125rem; text-transform: uppercase; letter-spacing: .1em; margin-bottom: .5rem;">
                <a href="{{ route('portal.kb') }}" style="color: rgba(255,255,255,.8);">{{ __('kb.portal_title') }}</a>
            </p>
            <h1 class="text-white font-bold" style="font-size: clamp(1.75rem, 4vw, 2.25rem)">{{ $category->name }}</h1>
            @if ($category->description)
                <p style="color: rgba(255,255,255,.78); margin-top: .75rem;">{{ $category->description }}</p>
            @endif
        </div>
    </section>

    <section class="max-w-3xl mx-auto" style="padding: 3rem 1.5rem;">
        @if ($articles->isEmpty())
            <p class="text-center text-sm text-gray-400 py-12">{{ __('kb.no_articles_in_category') }}</p>
        @else
            <div class="space-y-3">
                @foreach ($articles as $article)
                    <a href="{{ route('portal.kb.article', [$category, $article]) }}"
                       class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-300 hover:shadow-sm transition">
                        <h2 class="font-semibold text-gray-900">{{ $article->title }}</h2>
                        @if ($article->excerpt)
                            <p class="text-sm text-gray-500 mt-1 leading-relaxed">{{ Str::limit($article->excerpt, 160) }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif

        <div class="mt-8 text-center">
            <a href="{{ route('portal.kb') }}" class="text-sm text-gray-500 hover:text-gray-800">← {{ __('kb.back_to_categories') }}</a>
        </div>
    </section>

</x-portal-layout>
