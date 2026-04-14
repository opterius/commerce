<x-portal-layout>

    <section class="portal-hero" style="padding: 4rem 1.5rem;">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-white font-extrabold" style="font-size: clamp(1.75rem, 4vw, 2.5rem)">{{ __('faq.portal_title') }}</h1>
            <p style="color: rgba(255,255,255,.78); margin-top: .75rem; font-size: 1rem;">{{ __('faq.portal_subtitle') }}</p>
        </div>
    </section>

    <section class="max-w-3xl mx-auto" style="padding: 3rem 1.5rem;">
        @if ($faqs->isEmpty())
            <p class="text-center text-sm text-gray-400 py-12">{{ __('faq.no_faqs_yet') }}</p>
        @else
            <div x-data="{ open: null }" class="space-y-3">
                @foreach ($faqs as $index => $faq)
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <button
                            type="button"
                            @click="open === {{ $index }} ? open = null : open = {{ $index }}"
                            class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left hover:bg-gray-50 transition"
                        >
                            <span class="font-semibold text-gray-900 text-sm sm:text-base">{{ $faq->question }}</span>
                            <svg class="w-5 h-5 text-gray-400 transition-transform shrink-0"
                                 :class="open === {{ $index }} ? 'rotate-180' : ''"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $index }}" x-collapse x-cloak>
                            <div class="px-5 pb-5 pt-1 text-sm text-gray-600 leading-relaxed" style="white-space: pre-wrap;">{!! nl2br(e($faq->answer)) !!}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- CTA --}}
        <div class="mt-12 text-center p-8 bg-gray-50 rounded-xl">
            <p class="text-sm text-gray-600 mb-3">{{ __('faq.still_have_questions') }}</p>
            <a href="{{ route('client.login') }}" class="portal-btn" style="padding: .5rem 1.5rem;">{{ __('faq.contact_support') }}</a>
        </div>
    </section>

</x-portal-layout>
