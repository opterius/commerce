<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('email_templates.title') }}</h2>
            <a href="{{ route('admin.email-templates.create') }}">
                <x-button type="button">{{ __('email_templates.add_translation') }}</x-button>
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @foreach ($mailables as $key => $variables)
            @php $rows = $templates->get($key, collect()); @endphp
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="text-sm font-semibold text-gray-800">{{ __('email_templates.events.' . str_replace('.', '_', $key)) }}</span>
                        <span class="ml-2 text-xs font-mono text-gray-400">{{ $key }}</span>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">{{ __('email_templates.locale') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('email_templates.subject') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">{{ __('common.status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($rows as $template)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs font-mono font-semibold text-indigo-600 uppercase">{{ $template->locale }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">{{ $template->subject }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($template->is_active)
                                        <x-badge color="green">{{ __('common.active') }}</x-badge>
                                    @else
                                        <x-badge color="gray">{{ __('common.inactive') }}</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                    <a href="{{ route('admin.email-templates.edit', $template) }}"
                                       class="text-indigo-600 hover:text-indigo-900 font-medium">{{ __('common.edit') }}</a>
                                    <x-danger-button
                                        type="button"
                                        x-data=""
                                        x-on:click="$dispatch('open-modal', 'delete-tpl-{{ $template->id }}')"
                                        class="!px-3 !py-1 !text-xs"
                                    >{{ __('common.delete') }}</x-danger-button>
                                </td>
                            </tr>

                            @push('modals')
                                <x-delete-modal
                                    name="delete-tpl-{{ $template->id }}"
                                    :title="__('email_templates.delete_title')"
                                    :message="__('email_templates.delete_confirm')"
                                    :action="route('admin.email-templates.destroy', $template)"
                                />
                            @endpush
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-sm text-gray-400 italic">
                                    {{ __('email_templates.no_templates') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</x-admin-layout>
