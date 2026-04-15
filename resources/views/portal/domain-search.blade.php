<x-portal-layout>

<div class="max-w-3xl mx-auto px-4" style="padding-top:3rem; padding-bottom:4rem;">

    <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('portal.domain_search_title') }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ __('portal.domain_search_subtitle') }}</p>

    {{-- Search form --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('portal.domain.search') }}" class="flex items-end gap-3">
            <div class="flex-1">
                <label class="form-label">{{ __('domains.enter_domain_name') }}</label>
                <input type="text" name="q" value="{{ $sld }}" class="form-input w-full"
                    placeholder="yourdomain" autofocus>
            </div>
            <button type="submit" class="portal-btn" style="padding:.625rem 1.5rem;">
                {{ __('domains.check_availability') }}
            </button>
        </form>
    </div>

    @if ($error)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-800">
            {{ $error }}
        </div>
    @endif

    {{-- Results --}}
    @if (! empty($results))
        <div class="bg-white rounded-xl shadow-sm divide-y divide-gray-100">
            @foreach ($results as $row)
                <div class="flex items-center justify-between px-6 py-4 gap-4">
                    <div>
                        <span class="font-medium text-gray-900">{{ $row['domain_name'] }}</span>
                        @if ($row['error'])
                            <span class="ml-2 text-xs text-gray-400">({{ $row['error'] }})</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 flex-shrink-0">
                        @if ($row['available'])
                            <div class="text-right hidden sm:block">
                                <div class="text-sm text-gray-700">
                                    {{ $row['tld']->currency_code }} {{ $row['tld']->registerPriceFormatted() }}/yr
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ __('domains.available') }}
                            </span>
                            {{-- Link to register — auth middleware will redirect to login then back here --}}
                            <a href="{{ route('client.domains.register') }}?domain={{ urlencode($sld) }}&tld={{ urlencode($row['tld']->tld) }}"
                               class="portal-btn text-sm" style="padding:.4rem 1rem; font-size:.8125rem;">
                                {{ __('domains.register') }}
                            </a>
                        @elseif ($row['error'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                {{ __('domains.unknown') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ __('domains.taken') }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @elseif ($sld !== null && empty($results) && ! $error)
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
            {{ __('domains.no_results') }}
        </div>
    @endif

</div>

</x-portal-layout>
