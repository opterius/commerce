@php
    $portalSettings   = \App\Models\Setting::getGroup('portal');
    $brandingSettings = \App\Models\Setting::getGroup('branding');
    $accentColor = $portalSettings['portal_primary_color'] ?? '#4f46e5';
    $brandName   = $brandingSettings['brand_name'] ?? config('app.name', 'Opterius Commerce');
    $brandLogo   = $brandingSettings['brand_logo'] ?? null;
    $navLinks    = json_decode($portalSettings['portal_nav_links'] ?? '[]', true) ?? [];

    // Auto-built nav links for enabled content sections
    $autoLinks = [];
    if (($portalSettings['portal_show_kb']            ?? '0') === '1') {
        $autoLinks[] = ['label' => __('kb.portal_title'),            'url' => url('/kb'),            'open_new' => false];
    }
    if (($portalSettings['portal_show_faq']           ?? '0') === '1') {
        $autoLinks[] = ['label' => __('faq.portal_title'),           'url' => url('/faq'),           'open_new' => false];
    }
    if (($portalSettings['portal_show_announcements'] ?? '0') === '1') {
        $autoLinks[] = ['label' => __('announcements.portal_title'), 'url' => url('/announcements'), 'open_new' => false];
    }
    if (($portalSettings['portal_show_status']        ?? '0') === '1') {
        $autoLinks[] = ['label' => __('announcements.status_page'),  'url' => url('/status'),        'open_new' => false];
    }
    if (($portalSettings['portal_show_contact']       ?? '0') === '1') {
        $autoLinks[] = ['label' => __('contact.portal_title'),       'url' => url('/contact'),       'open_new' => false];
    }
    $allNavLinks = array_merge($navLinks, $autoLinks);

    // Featured announcement banner (shown across the portal)
    $bannerAnnouncements = [];
    if (($portalSettings['portal_show_announcements'] ?? '0') === '1') {
        $bannerAnnouncements = \App\Models\Announcement::active()->public()->featured()
            ->orderByDesc('published_at')
            ->limit(2)
            ->get();
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ $brandName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --pa: {{ $accentColor }};
            --pa-dark:  color-mix(in srgb, {{ $accentColor }} 78%, #000);
            --pa-light: color-mix(in srgb, {{ $accentColor }} 14%, #fff);
            --pa-xlight: color-mix(in srgb, {{ $accentColor }} 7%, #fff);
        }
        /* Buttons */
        .portal-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: .625rem 1.5rem;
            background-color: var(--pa); color: #fff;
            font-size: .875rem; font-weight: 600;
            border-radius: .5rem; border: none; cursor: pointer;
            text-decoration: none; transition: background-color .15s;
            white-space: nowrap;
        }
        .portal-btn:hover { background-color: var(--pa-dark); }
        .portal-btn-outline {
            display: inline-flex; align-items: center; justify-content: center;
            padding: .625rem 1.5rem;
            background: rgba(255,255,255,.15); color: #fff;
            font-size: .875rem; font-weight: 600;
            border-radius: .5rem; border: 1.5px solid rgba(255,255,255,.4);
            cursor: pointer; text-decoration: none; transition: background .15s;
            white-space: nowrap; backdrop-filter: blur(4px);
        }
        .portal-btn-outline:hover { background: rgba(255,255,255,.25); }
        .portal-btn-white {
            display: inline-flex; align-items: center; justify-content: center;
            padding: .625rem 1.5rem;
            background: #fff; color: var(--pa);
            font-size: .875rem; font-weight: 700;
            border-radius: .5rem; border: none; cursor: pointer;
            text-decoration: none; transition: background .15s;
            white-space: nowrap;
        }
        .portal-btn-white:hover { background: #f1f5f9; }
        /* Order card button */
        .portal-order-btn {
            display: block; width: 100%; text-align: center;
            padding: .5625rem 1rem;
            background-color: var(--pa); color: #fff;
            font-size: .875rem; font-weight: 600;
            border-radius: .5rem; text-decoration: none;
            transition: background-color .15s;
        }
        .portal-order-btn:hover { background-color: var(--pa-dark); }
        /* Hero */
        .portal-hero {
            background: linear-gradient(135deg, var(--pa) 0%, var(--pa-dark) 100%);
        }
        /* Feature icons */
        .portal-feature-icon {
            width: 2.75rem; height: 2.75rem; border-radius: .75rem;
            background-color: var(--pa-light);
            display: flex; align-items: center; justify-content: center;
        }
        .portal-feature-icon svg { color: var(--pa); }
        /* Domain search */
        .portal-search-btn {
            padding: .625rem 1.5rem;
            background-color: var(--pa); color: #fff;
            font-size: .9375rem; font-weight: 600;
            border-radius: 0 .5rem .5rem 0; border: none; cursor: pointer;
            transition: background-color .15s; white-space: nowrap;
        }
        .portal-search-btn:hover { background-color: var(--pa-dark); }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50" style="display:flex; flex-direction:column; min-height:100vh;">

    {{-- Featured announcement banner --}}
    @if (! empty($bannerAnnouncements) && count($bannerAnnouncements) > 0)
        @php
            $bannerStyles = [
                'info'     => ['bg' => '#eff6ff', 'text' => '#1e40af', 'accent' => '#3b82f6'],
                'success'  => ['bg' => '#ecfdf5', 'text' => '#065f46', 'accent' => '#10b981'],
                'warning'  => ['bg' => '#fffbeb', 'text' => '#92400e', 'accent' => '#f59e0b'],
                'critical' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'accent' => '#ef4444'],
            ];
        @endphp
        @foreach ($bannerAnnouncements as $ann)
            @php $bs = $bannerStyles[$ann->priority] ?? $bannerStyles['info']; @endphp
            <div style="background-color: {{ $bs['bg'] }}; color: {{ $bs['text'] }}; border-bottom: 1px solid rgba(0,0,0,.04);">
                <div class="max-w-6xl mx-auto" style="padding: .75rem 1.5rem; display:flex; align-items:center; gap:.75rem; font-size: .875rem;">
                    <span style="display:inline-block; width:.5rem; height:.5rem; border-radius:9999px; background-color: {{ $bs['accent'] }}; flex-shrink:0;"></span>
                    <span style="flex:1; min-width:0;">
                        <strong>{{ $ann->title }}</strong>
                        <span style="opacity:.8; margin-left:.5rem;">{{ Str::limit($ann->content, 120) }}</span>
                    </span>
                    <a href="{{ route('portal.announcement', $ann) }}"
                       style="font-weight:600; text-decoration: underline; white-space: nowrap; color: inherit;">
                        {{ __('announcements.read_more') }}
                    </a>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Sticky nav --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-6">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0">
                @if ($brandLogo)
                    <img src="{{ asset('storage/' . $brandLogo) }}" alt="{{ $brandName }}" class="h-8 w-auto">
                @else
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                         style="background-color: var(--pa)">
                        <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342
                                     1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0
                                     0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0
                                     .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504
                                     1.125 1.125v9.75c0 .621-.504 1.125-1.125
                                     1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0
                                     0H3.75m0 0h-.375a1.125 1.125 0 0
                                     1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0
                                     3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6
                                     0Zm3 0h.008v.008H18V10.5Zm-12
                                     0h.008v.008H6V10.5Z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-900 text-lg">{{ $brandName }}</span>
                @endif
            </a>

            {{-- Custom + auto-built nav links (desktop) --}}
            @if (count($allNavLinks) > 0)
                <nav class="hidden md:flex items-center gap-1 flex-1">
                    @foreach ($allNavLinks as $link)
                        <a href="{{ $link['url'] }}"
                           {{ ($link['open_new'] ?? false) ? 'target="_blank" rel="noopener"' : '' }}
                           class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>
            @else
                <div class="flex-1"></div>
            @endif

            {{-- Auth --}}
            <div class="flex items-center gap-3 shrink-0">
                @auth('client')
                    <a href="{{ route('client.dashboard') }}" class="portal-btn" style="padding:.5rem 1.25rem">
                        {{ __('common.client_area') }}
                    </a>
                @else
                    <a href="{{ route('client.login') }}"
                       class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">
                        {{ __('auth.sign_in') }}
                    </a>
                    <a href="{{ route('client.login') }}" class="portal-btn" style="padding:.5rem 1.25rem">
                        {{ __('auth.get_started') }}
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @include('partials.flash-messages')

    {{-- Main content (flex-1 pushes footer down) --}}
    <main style="flex:1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer style="background:#fff; border-top:1px solid #e5e7eb; margin-top:4rem;">
        <div class="max-w-6xl mx-auto" style="padding: 2.5rem 1.5rem;">

            {{-- Top row: brand left, links right --}}
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem;">

                {{-- Brand --}}
                <div style="display:flex; align-items:center; gap:.625rem;">
                    <div style="width:1.75rem; height:1.75rem; border-radius:.375rem; background-color:var(--pa); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg style="width:.9rem; height:.9rem; color:#fff;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                        </svg>
                    </div>
                    <span style="font-weight:600; color:#111827; font-size:.9375rem;">{{ $brandName }}</span>
                </div>

                {{-- Nav + auth links --}}
                <nav style="display:flex; align-items:center; flex-wrap:wrap; gap:1.5rem;">
                    @foreach ($allNavLinks as $link)
                        <a href="{{ $link['url'] }}"
                           {{ ($link['open_new'] ?? false) ? 'target="_blank" rel="noopener"' : '' }}
                           style="font-size:.875rem; color:#6b7280; text-decoration:none;"
                           onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                    <a href="{{ route('client.login') }}"
                       style="font-size:.875rem; color:#6b7280; text-decoration:none;"
                       onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">
                        {{ __('auth.sign_in') }}
                    </a>
                    <a href="{{ route('client.login') }}" class="portal-btn" style="padding:.4375rem 1.125rem; font-size:.8125rem;">
                        {{ __('auth.get_started') }}
                    </a>
                </nav>
            </div>

            {{-- Bottom row: copyright --}}
            <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid #f3f4f6;">
                <p style="font-size:.75rem; color:#9ca3af;">© {{ date('Y') }} {{ $brandName }}. All rights reserved.</p>
            </div>

        </div>
    </footer>

    @stack('modals')
</body>
</html>
