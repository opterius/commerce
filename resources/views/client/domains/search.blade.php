<x-client-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('domains.search_domains') }}</h2>
    </x-slot>

    <div class="space-y-6">

        {{-- Search form --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" action="{{ route('client.domains.search') }}" class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="form-label">{{ __('domains.enter_domain_name') }}</label>
                    <div class="relative">
                        <input type="text" name="domain" value="{{ $sld }}" class="form-input w-full pl-4 pr-4" placeholder="yourdomain" autofocus>
                    </div>
                </div>
                <button type="submit" class="btn-primary">{{ __('domains.check_availability') }}</button>
            </form>
        </div>

        {{-- Results --}}
        @if (! empty($results))
            <div class="bg-white rounded-xl shadow-sm divide-y divide-gray-100">
                @foreach ($results as $row)
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <span class="font-medium text-gray-900">{{ $row['domain_name'] }}</span>
                            @if ($row['error'])
                                <span class="ml-2 text-xs text-gray-400">({{ $row['error'] }})</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-4">
                            @if ($row['available'])
                                <div class="text-right">
                                    <div class="text-sm text-gray-700">
                                        {{ $row['tld']->currency_code }} {{ $row['tld']->registerPriceFormatted() }}/yr
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('domains.available') }}
                                </span>
                                <a href="{{ route('client.domains.register') }}?domain={{ $sld }}&tld={{ $row['tld']->tld }}"
                                   class="btn-primary text-sm">
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
        @elseif ($sld !== null && empty($results))
            <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
                {{ __('domains.no_results') }}
            </div>
        @endif

    </div>
</x-client-layout>
