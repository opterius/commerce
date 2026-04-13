<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-6">{{ __('tickets.settings') }}</h3>

    <form method="POST" action="{{ route('admin.settings.update', 'tickets') }}">
        @csrf

        <div class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input name="ticket_auto_close_days" type="number" :label="__('tickets.auto_close_days')"
                        :value="$settings['ticket_auto_close_days'] ?? 5" min="0" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('tickets.auto_close_help') }}</p>
                </div>

                <div>
                    <label class="form-label">{{ __('tickets.default_priority') }}</label>
                    <select name="ticket_default_priority" class="form-input">
                        @foreach (\App\Models\Ticket::PRIORITIES as $key => $label)
                            <option value="{{ $key }}" {{ ($settings['ticket_default_priority'] ?? 'medium') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input name="ticket_max_attachment_kb" type="number" :label="__('tickets.max_attachment_kb')"
                        :value="$settings['ticket_max_attachment_kb'] ?? 10240" min="0" />
                </div>

                <div>
                    <x-input name="ticket_allowed_extensions" :label="__('tickets.allowed_extensions')"
                        :value="$settings['ticket_allowed_extensions'] ?? 'jpg,jpeg,png,gif,pdf,txt,zip,doc,docx'" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('tickets.allowed_extensions_help') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-100">
            <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
        </div>
    </form>
</div>
