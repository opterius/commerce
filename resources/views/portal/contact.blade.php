<x-portal-layout>

    <section class="portal-hero" style="padding: 4rem 1.5rem;">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-white font-extrabold" style="font-size: clamp(1.75rem, 4vw, 2.5rem)">{{ __('contact.portal_title') }}</h1>
            <p style="color: rgba(255,255,255,.78); margin-top: .75rem; font-size: 1rem;">{{ __('contact.portal_subtitle') }}</p>
        </div>
    </section>

    <section class="max-w-2xl mx-auto" style="padding: 3rem 1.5rem;">

        <div class="bg-white rounded-2xl border border-gray-200 p-8">
            <form method="POST" action="{{ route('portal.contact.submit') }}" class="space-y-5">
                @csrf

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                    <div>
                        <label class="form-label" for="name">{{ __('common.name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="255" class="form-input">
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label" for="email">{{ __('common.email') }} <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required maxlength="255" class="form-input">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="form-label" for="subject">{{ __('contact.subject') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required maxlength="255" class="form-input">
                    @error('subject')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="message">{{ __('contact.message_body') }} <span class="text-red-500">*</span></label>
                    <textarea name="message" id="message" rows="6" required maxlength="5000" class="form-input">{{ old('message') }}</textarea>
                    @error('message')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="portal-btn" style="width: 100%; padding: .8125rem;">
                        {{ __('contact.send_message') }}
                    </button>
                </div>
            </form>
        </div>

    </section>

</x-portal-layout>
